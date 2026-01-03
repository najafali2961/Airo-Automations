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
        
        $productId = $payload['id'] ?? $payload['admin_graphql_api_id'] ?? $settings['product_id'] ?? null;
        $tagsToRemove = $settings['tags'] ?? $settings['tag'] ?? null;

        if (!$productId) {
            $this->log($execution, $node->id, 'error', "Missing Product ID.");
            return;
        }

        if (!$tagsToRemove) {
            $this->log($execution, $node->id, 'warning', "No tags provided to remove.");
            return;
        }

        if (is_string($productId) && strpos($productId, 'gid://') === 0) {
            $productId = (int) basename($productId);
        }

        $this->log($execution, $node->id, 'info', "Removing tags from product #{$productId}: {$tagsToRemove}");

        $apiVersion = config('shopify-app.api_version', '2025-10');
        
        $response = $shop->api()->rest('GET', "/admin/api/{$apiVersion}/products/{$productId}.json", ['fields' => 'id,tags']);
        
        if ($response['errors']) {
             $this->log($execution, $node->id, 'error', "Failed to fetch product: " . json_encode($response['errors']));
             return;
        }

        $product = $response['body']['product'];
        $currentTags = array_filter(array_map('trim', explode(',', $product['tags'] ?? '')));
        $toRemove = array_filter(array_map('trim', explode(',', $tagsToRemove)));
        
        $newTags = array_diff($currentTags, $toRemove);

        if (count($newTags) === count($currentTags)) {
            $this->log($execution, $node->id, 'info', "Tags not found. Skipping update.");
            return;
        }

        $updateResponse = $shop->api()->rest('PUT', "/admin/api/{$apiVersion}/products/{$productId}.json", [
            'product' => [
                'id' => $productId,
                'tags' => implode(', ', $newTags)
            ]
        ]);

        if ($updateResponse['errors']) {
            $this->log($execution, $node->id, 'error', "Failed to update product tags: " . json_encode($updateResponse['errors']));
        } else {
            $this->log($execution, $node->id, 'info', "Successfully removed tags for product #{$productId}");
        }
    }
}
