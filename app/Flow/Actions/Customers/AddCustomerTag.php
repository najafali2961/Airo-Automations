<?php

namespace App\Flow\Actions\Customers;

use App\Flow\Actions\BaseAction;
use App\Models\Node;
use App\Models\Execution;

class AddCustomerTag extends BaseAction
{
    public function handle(Node $node, array $payload, Execution $execution): void
    {
        $shop = $this->getShop($execution);
        $settings = $this->getSettings($node);
        
        // Customers create webhook has customer data as root, Orders create has 'customer' nested.
        $customerId = $payload['customer']['id'] ?? $payload['id'] ?? $settings['customer_id'] ?? null;
        $tagsToAdd = $settings['tag'] ?? $settings['tags'] ?? null;

        if (!$customerId) {
            $this->log($execution, $node->id, 'error', "Missing Customer ID.");
            return;
        }

        if (!$tagsToAdd) {
            $this->log($execution, $node->id, 'warning', "No tags provided.");
            return;
        }

        if (is_string($customerId) && strpos($customerId, 'gid://') === 0) {
            $customerId = (int) basename($customerId);
        }

        $this->log($execution, $node->id, 'info', "Adding tags to customer #{$customerId}: {$tagsToAdd}");

        $apiVersion = config('shopify-app.api_version', '2025-10');
        
        $response = $shop->api()->rest('GET', "/admin/api/{$apiVersion}/customers/{$customerId}.json", ['fields' => 'id,tags']);
        
        if ($response['errors']) {
             $this->log($execution, $node->id, 'error', "Failed to fetch customer: " . json_encode($response['errors']));
             return;
        }

        $customer = $response['body']['customer'];
        $currentTags = array_filter(array_map('trim', explode(',', $customer['tags'] ?? '')));
        $newTags = array_filter(array_map('trim', explode(',', $tagsToAdd)));
        
        $mergedTags = array_unique(array_merge($currentTags, $newTags));

        if (count($mergedTags) === count($currentTags)) {
            $this->log($execution, $node->id, 'info', "Tags already present. Skipping.");
            return;
        }

        $updateResponse = $shop->api()->rest('PUT', "/admin/api/{$apiVersion}/customers/{$customerId}.json", [
            'customer' => [
                'id' => $customerId,
                'tags' => implode(', ', $mergedTags)
            ]
        ]);

        if ($updateResponse['errors']) {
            $this->log($execution, $node->id, 'error', "Failed to update customer tags: " . json_encode($updateResponse['errors']));
        } else {
            $this->log($execution, $node->id, 'info', "Successfully updated tags for customer #{$customerId}");
        }
    }
}
