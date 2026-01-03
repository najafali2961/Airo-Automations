<?php

namespace App\Flow\Actions\Orders;

use App\Flow\Actions\BaseAction;
use App\Models\Node;
use App\Models\Execution;

class RemoveOrderTag extends BaseAction
{
    public function handle(Node $node, array $payload, Execution $execution): void
    {
        $shop = $this->getShop($execution);
        $settings = $this->getSettings($node);
        
        $orderId = $payload['admin_graphql_api_id'] ?? $settings['order_id'] ?? null;
        
        // If we only have numeric ID, convert to GID
        if ($orderId && !str_starts_with((string)$orderId, 'gid://')) {
            $orderId = "gid://shopify/Order/{$orderId}";
        }

        if (!$orderId && !empty($payload['id'])) {
             $orderId = "gid://shopify/Order/" . $payload['id'];
        }

        $tagsToRemove = $settings['tag'] ?? $settings['tags'] ?? null;

        if (!$orderId) {
            $this->log($execution, $node->id, 'error', "Missing Order ID in payload or settings.");
            return;
        }

        if (!$tagsToRemove) {
            $this->log($execution, $node->id, 'warning', "No tags provided to remove.");
            return;
        }

        $tagsArray = array_filter(array_map('trim', explode(',', $tagsToRemove)));

        $this->log($execution, $node->id, 'info', "Removing tags from order {$orderId}: " . implode(', ', $tagsArray));

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
            'id' => $orderId,
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
            $this->log($execution, $node->id, 'info', "Successfully removed tags from order.");
        }
    }
}
