<?php

namespace App\Flow\Actions\Orders;

use App\Flow\Actions\BaseAction;
use App\Models\Node;
use App\Models\Execution;
use Illuminate\Support\Facades\Log;

class RemoveOrderTag extends BaseAction
{
    public function handle(Node $node, array $payload, Execution $execution): void
    {
        $shop = $this->getShop($execution);
        $settings = $node->settings['form'] ?? $node->settings;
        
        $orderId = $payload['id'] ?? $payload['admin_graphql_api_id'] ?? $settings['order_id'] ?? null;
        $tagsToRemove = $settings['tags'] ?? $settings['tag'] ?? null;

        if (!$orderId) {
            $this->log($execution, $node->id, 'error', "Missing Order ID.");
            return;
        }

        if (!$tagsToRemove) {
            $this->log($execution, $node->id, 'warning', "No tags provided to remove.");
            return;
        }

        if (is_string($orderId) && strpos($orderId, 'gid://') === 0) {
            $orderId = (int) basename($orderId);
        }

        $this->log($execution, $node->id, 'info', "Removing tags from order #{$orderId}: {$tagsToRemove}");

        $apiVersion = config('shopify-app.api_version', '2025-10');
        
        $response = $shop->api()->rest('GET', "/admin/api/{$apiVersion}/orders/{$orderId}.json", ['fields' => 'id,tags']);
        
        if ($response['errors']) {
             $this->log($execution, $node->id, 'error', "Failed to fetch order: " . json_encode($response['errors']));
             return;
        }

        $order = $response['body']['order'];
        $currentTags = array_filter(array_map('trim', explode(',', $order['tags'] ?? '')));
        $toRemove = array_filter(array_map('trim', explode(',', $tagsToRemove)));
        
        $newTags = array_diff($currentTags, $toRemove);

        if (count($newTags) === count($currentTags)) {
            $this->log($execution, $node->id, 'info', "Tags not found on order. Skipping update.");
            return;
        }

        $updateResponse = $shop->api()->rest('PUT', "/admin/api/{$apiVersion}/orders/{$orderId}.json", [
            'order' => [
                'id' => $orderId,
                'tags' => implode(', ', $newTags)
            ]
        ]);

        if ($updateResponse['errors']) {
            $this->log($execution, $node->id, 'error', "Failed to update order tags: " . json_encode($updateResponse['errors']));
        } else {
            $this->log($execution, $node->id, 'info', "Successfully removed tags for order #{$orderId}");
        }
    }
}
