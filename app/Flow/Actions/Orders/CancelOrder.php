<?php

namespace App\Flow\Actions\Orders;

use App\Flow\Actions\BaseAction;
use App\Models\Node;
use App\Models\Execution;

class CancelOrder extends BaseAction
{
    public function handle(Node $node, array $payload, Execution $execution): void
    {
        $shop = $this->getShop($execution);
        $settings = $node->settings['form'] ?? $node->settings;
        
        $orderId = $payload['id'] ?? $payload['admin_graphql_api_id'] ?? $settings['order_id'] ?? null;
        $reason = $settings['reason'] ?? 'other'; // customer, inventory, fraud, other, declined
        $note = $settings['note'] ?? 'Cancelled via Automation';

        if (!$orderId) {
            $this->log($execution, $node->id, 'error', "Missing Order ID.");
            return;
        }

        if (is_string($orderId) && strpos($orderId, 'gid://') === 0) {
            $orderId = (int) basename($orderId);
        }

        $this->log($execution, $node->id, 'info', "Attempting to cancel order #{$orderId} (Reason: {$reason})");

        $apiVersion = config('shopify-app.api_version', '2025-10');
        
        $response = $shop->api()->rest('POST', "/admin/api/{$apiVersion}/orders/{$orderId}/cancel.json", [
            'reason' => $reason,
            'note' => $note,
            'email' => $settings['email_customer'] ?? false
        ]);

        if ($response['errors']) {
             $this->log($execution, $node->id, 'error', "Failed to cancel order: " . json_encode($response['errors']));
        } else {
             $this->log($execution, $node->id, 'info', "Order #{$orderId} cancelled successfully.");
        }
    }
}
