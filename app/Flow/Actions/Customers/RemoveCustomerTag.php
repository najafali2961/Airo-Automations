<?php

namespace App\Flow\Actions\Customers;

use App\Flow\Actions\BaseAction;
use App\Models\Node;
use App\Models\Execution;

class RemoveCustomerTag extends BaseAction
{
    public function handle(Node $node, array $payload, Execution $execution): void
    {
        $shop = $this->getShop($execution);
        $settings = $this->getSettings($node);
        
        $customerId = $payload['customer']['admin_graphql_api_id'] ?? 
                      $payload['admin_graphql_api_id'] ?? 
                      $settings['customer_id'] ?? 
                      null;

        if (!$customerId && !empty($payload['customer']['id'])) {
            $customerId = "gid://shopify/Customer/" . $payload['customer']['id'];
        } elseif (!$customerId && !empty($payload['id'])) {
            $customerId = "gid://shopify/Customer/" . $payload['id'];
        }

        if ($customerId && !str_starts_with((string)$customerId, 'gid://')) {
            $customerId = "gid://shopify/Customer/{$customerId}";
        }

        $tagsToRemove = $settings['tag'] ?? $settings['tags'] ?? null;

        if (!$customerId) {
            $this->log($execution, $node->id, 'error', "Missing Customer ID in payload or settings.");
            return;
        }

        if (!$tagsToRemove) {
            $this->log($execution, $node->id, 'warning', "No tags provided to remove.");
            return;
        }

        $tagsArray = array_filter(array_map('trim', explode(',', $tagsToRemove)));

        $this->log($execution, $node->id, 'info', "Removing tags from customer {$customerId}: " . implode(', ', $tagsArray));

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
            'id' => $customerId,
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
            $this->log($execution, $node->id, 'info', "Successfully removed tags from customer.");
        }
    }
}
