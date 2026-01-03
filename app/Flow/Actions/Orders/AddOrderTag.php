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
        
        $orderId = $payload['id'] ?? $payload['admin_graphql_api_id'] ?? $settings['order_id'] ?? null;
        $tagsToAdd = $settings['tag'] ?? $settings['tags'] ?? null;

        if (!$orderId) {
            $this->log($execution, $node->id, 'error', "Missing Order ID in payload or settings.");
            return;
        }

        if (!$tagsToAdd) {
            $this->log($execution, $node->id, 'warning', "No tags provided to add.");
            return;
        }

        // Clean ID if GID
        if (is_string($orderId) && strpos($orderId, 'gid://') === 0) {
            $orderId = (int) basename($orderId);
        }

        $this->log($execution, $node->id, 'info', "Adding tags to order #{$orderId}: {$tagsToAdd}");

        $apiVersion = config('shopify-app.api_version', '2025-10');
        
        // 1. Fetch current tags
        $response = $shop->api()->rest('GET', "/admin/api/{$apiVersion}/orders/{$orderId}.json", ['fields' => 'id,tags']);
        
        if ($response['errors']) {
             $this->log($execution, $node->id, 'error', "Failed to fetch order: " . json_encode($response['errors']));
             return;
        }

        $order = $response['body']['order'];
        $currentTags = array_filter(array_map('trim', explode(',', $order['tags'] ?? '')));
        $newTags = array_filter(array_map('trim', explode(',', $tagsToAdd)));
        
        $mergedTags = array_unique(array_merge($currentTags, $newTags));

        if (count($mergedTags) === count($currentTags)) {
            $this->log($execution, $node->id, 'info', "All tags already present on order. Skipping update.");
            return;
        }

        // 2. Update order
        $updateResponse = $shop->api()->rest('PUT', "/admin/api/{$apiVersion}/orders/{$orderId}.json", [
            'order' => [
                'id' => $orderId,
                'tags' => implode(', ', $mergedTags)
            ]
        ]);

        if ($updateResponse['errors']) {
            $this->log($execution, $node->id, 'error', "Failed to update order tags: " . json_encode($updateResponse['errors']));
        } else {
            $this->log($execution, $node->id, 'info', "Successfully updated tags for order #{$orderId}");
        }
    }
}
