<?php

namespace App\Flow\Actions\Products;

use App\Flow\Actions\BaseAction;
use App\Models\Node;
use App\Models\Execution;

class UpdateProductStatus extends BaseAction
{
    public function handle(Node $node, array $payload, Execution $execution): void
    {
        $shop = $this->getShop($execution);
        $settings = $node->settings['form'] ?? $node->settings;
        
        $productId = $payload['id'] ?? $payload['admin_graphql_api_id'] ?? $settings['product_id'] ?? null;
        $status = $settings['status'] ?? 'draft'; // active, draft, archived

        if (!$productId) {
            $this->log($execution, $node->id, 'error', "Missing Product ID.");
            return;
        }

        if (is_string($productId) && strpos($productId, 'gid://') === 0) {
            $productId = (int) basename($productId);
        }

        $this->log($execution, $node->id, 'info', "Updating product #{$productId} status to: {$status}");

        $apiVersion = config('shopify-app.api_version', '2025-10');
        
        $updateResponse = $shop->api()->rest('PUT', "/admin/api/{$apiVersion}/products/{$productId}.json", [
            'product' => [
                'id' => $productId,
                'status' => $status
            ]
        ]);

        if ($updateResponse['errors']) {
            $this->log($execution, $node->id, 'error', "Failed to update product status: " . json_encode($updateResponse['errors']));
        } else {
            $this->log($execution, $node->id, 'info', "Successfully updated status for product #{$productId}");
        }
    }
}
