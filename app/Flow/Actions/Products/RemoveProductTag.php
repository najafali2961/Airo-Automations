<?php

namespace App\Flow\Actions\Products;

use App\Flow\Actions\BaseAction;
use App\Models\Node;
use App\Models\Execution;

class RemoveProductTag extends BaseAction
{
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

        $tagsToRemove = $settings['tag'] ?? $settings['tags'] ?? null;

        if (!$productId) {
            $this->log($execution, $node->id, 'error', "Missing Product ID in payload or settings.");
            return;
        }

        if (!$tagsToRemove) {
            $this->log($execution, $node->id, 'warning', "No tags provided to remove.");
            return;
        }

        $tagsArray = array_filter(array_map('trim', explode(',', $tagsToRemove)));

        $this->log($execution, $node->id, 'info', "Removing tags from product {$productId}: " . implode(', ', $tagsArray));

        $query = <<<'GQL'
mutation tagsRemove($id: ID!, $tags: [String!]!) {
  tagsRemove(id: $id, tags: $tags) {
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

        $userErrors = $response['body']['data']['tagsRemove']['userErrors'] ?? [];
        if (!empty($userErrors)) {
            $this->log($execution, $node->id, 'error', "Shopify Error: " . json_encode($userErrors));
        } else {
            $this->log($execution, $node->id, 'info', "Successfully removed tags from product.");
        }
    }
}
