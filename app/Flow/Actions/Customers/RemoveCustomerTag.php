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
        
        $customerId = $payload['id'] ?? $payload['admin_graphql_api_id'] ?? $settings['customer_id'] ?? null;
        $tagsToRemove = $settings['tags'] ?? $settings['tag'] ?? null;

        if (!$customerId) {
            $this->log($execution, $node->id, 'error', "Missing Customer ID.");
            return;
        }

        if (!$tagsToRemove) {
            $this->log($execution, $node->id, 'warning', "No tags provided to remove.");
            return;
        }

        if (is_string($customerId) && strpos($customerId, 'gid://') === 0) {
            $customerId = (int) basename($customerId);
        }

        $this->log($execution, $node->id, 'info', "Removing tags from customer #{$customerId}: {$tagsToRemove}");

        $apiVersion = config('shopify-app.api_version', '2025-10');
        
        $response = $shop->api()->rest('GET', "/admin/api/{$apiVersion}/customers/{$customerId}.json", ['fields' => 'id,tags']);
        
        if ($response['errors']) {
             $this->log($execution, $node->id, 'error', "Failed to fetch customer: " . json_encode($response['errors']));
             return;
        }

        $customer = $response['body']['customer'];
        $currentTags = array_filter(array_map('trim', explode(',', $customer['tags'] ?? '')));
        $toRemove = array_filter(array_map('trim', explode(',', $tagsToRemove)));
        
        $newTags = array_diff($currentTags, $toRemove);

        if (count($newTags) === count($currentTags)) {
            $this->log($execution, $node->id, 'info', "Tags not found. Skipping update.");
            return;
        }

        $updateResponse = $shop->api()->rest('PUT', "/admin/api/{$apiVersion}/customers/{$customerId}.json", [
            'customer' => [
                'id' => $customerId,
                'tags' => implode(', ', $newTags)
            ]
        ]);

        if ($updateResponse['errors']) {
            $this->log($execution, $node->id, 'error', "Failed to update customer tags: " . json_encode($updateResponse['errors']));
        } else {
            $this->log($execution, $node->id, 'info', "Successfully removed tags for customer #{$customerId}");
        }
    }
}
