<?php

namespace App\Flow\Actions\Products;

use App\Flow\Actions\BaseAction;
use App\Models\Node;
use App\Models\Execution;
use App\Services\VariableService;

class AddProductTag extends BaseAction
{
    protected $variableService;

    public function __construct(VariableService $variableService)
    {
        $this->variableService = $variableService;
    }

    public function handle(Node $node, array $payload, Execution $execution): void
    {
        $shop = $this->getShop($execution);
        $settings = $this->getSettings($node);
        
        $productId = $payload['admin_graphql_api_id'] ?? $settings['product_id'] ?? null;

        if (!$productId && !empty($payload['id'])) {
            $productId = "gid://shopify/Product/" . $payload['id'];
        }

        if ($productId && !str_starts_with((string)$productId, 'gid://')) {
            $productId = "gid://shopify/Product/{$productId}";
        }

        $tagsToAdd = $settings['tag'] ?? $settings['tags'] ?? null;

        if (!$productId) {
            $this->log($execution, $node->id, 'error', "Missing Product ID in payload or settings.");
            return;
        }

        if (!$tagsToAdd) {
            $this->log($execution, $node->id, 'warning', "No tags provided to add.");
            return;
        }

        $tagsToAdd = $this->variableService->replace($tagsToAdd, $payload);
        $tagsArray = array_filter(array_map('trim', explode(',', $tagsToAdd)));

        $this->log($execution, $node->id, 'info', "Adding tags to product {$productId}: " . implode(', ', $tagsArray));

        $query = <<<'GQL'
mutation tagsAdd($id: ID!, $tags: [String!]!) {
  tagsAdd(id: $id, tags: $tags) {
    node {
      id
    }
    userErrors {
      field
      message
    }
  }
}
GQL;

        $variables = [
            'id' => $productId,
            'tags' => $tagsArray
        ];

        $response = $shop->api()->graph($query, $variables);

        if ($response['errors']) {
             $this->log($execution, $node->id, 'error', "GraphQL Error: " . json_encode($response['errors']));
             return;
        }

        $userErrors = $response['body']['data']['tagsAdd']['userErrors'] ?? [];
        if (!empty($userErrors)) {
            $this->log($execution, $node->id, 'error', "Shopify Error: " . json_encode($userErrors));
        } elseif (isset($response['body']['errors'])) {
             // Catch top-level GraphQL errors that might not be in userErrors
             $this->log($execution, $node->id, 'error', "Shopify API Error: " . json_encode($response['body']['errors']));
        } elseif (!isset($response['body']['data']['tagsAdd'])) {
             // Catch unexpected response structure
             $this->log($execution, $node->id, 'error', "Unexpected Shopify Response: " . json_encode($response['body']));
        } else {
            $this->log($execution, $node->id, 'info', "Successfully added tags to product.");
        }
    }
}
