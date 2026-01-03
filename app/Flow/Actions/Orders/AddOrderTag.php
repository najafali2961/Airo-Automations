<?php

namespace App\Flow\Actions\Orders;

use App\Flow\Actions\BaseAction;
use App\Models\Node;
use App\Models\Execution;
use Illuminate\Support\Facades\Log;

class AddOrderTag extends BaseAction
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

        $tagsToAdd = $settings['tag'] ?? $settings['tags'] ?? null;

        if (!$orderId) {
            $this->log($execution, $node->id, 'error', "Missing Order ID in payload or settings.");
            return;
        }

        if (!$tagsToAdd) {
            $this->log($execution, $node->id, 'warning', "No tags provided to add.");
            return;
        }

        $tagsArray = array_filter(array_map('trim', explode(',', $tagsToAdd)));

        $this->log($execution, $node->id, 'info', "Adding tags to order {$orderId}: " . implode(', ', $tagsArray));

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
            'id' => $orderId,
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
            $this->log($execution, $node->id, 'info', "Successfully added tags to order.");
        }
    }
}
