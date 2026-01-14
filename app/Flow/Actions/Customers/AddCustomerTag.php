<?php

namespace App\Flow\Actions\Customers;

use App\Flow\Actions\BaseAction;
use App\Models\Node;
use App\Models\Execution;
use App\Services\VariableService;

class AddCustomerTag extends BaseAction
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
        
        // Try to get customer ID from various payload locations or settings
        $customerId = $payload['customer']['admin_graphql_api_id'] ?? 
                      $payload['admin_graphql_api_id'] ?? 
                      $settings['customer_id'] ?? 
                      null;

        // Fallback for numeric ID
        if (!$customerId && !empty($payload['customer']['id'])) {
            $customerId = "gid://shopify/Customer/" . $payload['customer']['id'];
        } elseif (!$customerId && !empty($payload['id'])) {
            $customerId = "gid://shopify/Customer/" . $payload['id'];
        }

        // If it's numeric, convert to GID
        if ($customerId && !str_starts_with((string)$customerId, 'gid://')) {
            $customerId = "gid://shopify/Customer/{$customerId}";
        }

        $tagsToAdd = $settings['tag'] ?? $settings['tags'] ?? null;

        if (!$customerId) {
            $this->log($execution, $node->id, 'error', "Missing Customer ID in payload or settings.");
            return;
        }

        if (!$tagsToAdd) {
            $this->log($execution, $node->id, 'warning', "No tags provided to add.");
            return;
        }

        $tagsToAdd = $this->variableService->replace($tagsToAdd, $payload);
        $tagsArray = array_filter(array_map('trim', explode(',', $tagsToAdd)));

        $this->log($execution, $node->id, 'info', "Adding tags to customer {$customerId}: " . implode(', ', $tagsArray));

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
            'id' => $customerId,
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
        } else {
            $this->log($execution, $node->id, 'info', "Successfully added tags to customer.");
        }
    }
}
