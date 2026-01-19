<?php

namespace App\Flow\Actions\Shopify;

use App\Flow\Actions\BaseAction;
use App\Models\Node;
use App\Models\Execution;
use Illuminate\Support\Facades\Log;

class ShopifyGqlAction extends BaseAction
{
    /**
     * Map action keys to GraphQL mutations.
     */
    protected static $mutationMap = [
        'archive_order' => 'orderClose',
        'unarchive_order' => 'orderOpen',
        'hold_fulfillment' => 'fulfillmentOrderHold',
        'release_hold' => 'fulfillmentOrderReleaseHold',
        'delete_product' => 'productDelete',
        'enable_customer' => 'customerUpdate',
        'disable_customer' => 'customerUpdate',
        'add_to_collection' => 'collectionAddProducts',
        'capture_payment' => 'orderPaymentUpsert', 
        'adjust_inventory' => 'inventoryAdjustQuantities',
        'create_basic_discount' => 'discountCodeBasicCreate',
        'update_variant_price' => 'productVariantsBulkUpdate',
        'cancel_order' => 'orderCancel',
    ];

    public function handle(Node $node, array $payload, Execution $execution): void
    {
        $shop = $this->getShop($execution);
        $actionKey = $node->settings['action'] ?? null;
        $settings = $this->getSettings($node);

        if (!isset(self::$mutationMap[$actionKey])) {
            $this->log($execution, $node->id, 'error', "No GraphQL mapping for action: {$actionKey}");
            return;
        }

        $mutationName = self::$mutationMap[$actionKey];
        $variables = $this->prepareVariables($actionKey, $settings, $payload);

        if (empty($variables)) {
            $this->log($execution, $node->id, 'error', "Could not resolve necessary IDs or data for action: {$actionKey}");
            return;
        }

        $this->log($execution, $node->id, 'info', "Executing Shopify GraphQL mutation: {$mutationName}");
        $query = $this->getQueryForMutation($mutationName);

        $response = $shop->api()->graph($query, $variables);

        if ($response['errors']) {
            $this->log($execution, $node->id, 'error', "GraphQL Error: " . json_encode($response['errors']));
            return;
        }

        $userData = $response['body']['data'][$mutationName] ?? null;
        if ($userData && !empty($userData['userErrors'])) {
            $this->log($execution, $node->id, 'error', "Shopify User Errors: " . json_encode($userData['userErrors']));
        } else {
            $this->log($execution, $node->id, 'info', "Successfully executed {$actionKey}");
        }
    }

    protected function prepareVariables($actionKey, $settings, $payload)
    {
        $id = $payload['admin_graphql_api_id'] ?? $payload['id'] ?? null;
        
        switch ($actionKey) {
            case 'archive_order':
            case 'unarchive_order':
                return ['input' => ['id' => $this->ensureGid($id, 'Order')]];
            
            case 'delete_product':
                return ['input' => ['id' => $this->ensureGid($id, 'Product')]];

            case 'add_to_collection':
                return [
                    'id' => $this->ensureGid($settings['collection_id'], 'Collection'),
                    'productIds' => [$this->ensureGid($id, 'Product')]
                ];
            
            case 'hold_fulfillment':
                return [
                    'fulfillmentOrderId' => $this->ensureGid($id, 'FulfillmentOrder'),
                    'fulfillmentOrderLineItemsToHold' => [],
                    'reason' => 'OTHER',
                    'reasonNotes' => $settings['reason'] ?? 'Hold by Automation'
                ];

            case 'cancel_order':
                return [
                    'id' => $this->ensureGid($id, 'Order'),
                    'reason' => strtoupper($settings['reason'] ?? 'OTHER'),
                    'note' => $settings['note'] ?? ''
                ];

            case 'capture_payment':
                return [
                    'id' => $this->ensureGid($id, 'Order')
                ];

            case 'enable_customer':
                return [
                    'input' => [
                        'id' => $this->ensureGid($id, 'Customer'),
                        'note' => 'Account enabled by automation'
                    ]
                ];

            case 'disable_customer':
                return [
                    'input' => [
                        'id' => $this->ensureGid($id, 'Customer'),
                        'note' => 'Account disabled by automation'
                    ]
                ];

            case 'adjust_inventory':
                return [
                    'input' => [
                        'reason' => 'correction',
                        'name' => 'available',
                        'changes' => [
                            [
                                'inventoryItemId' => $this->ensureGid($settings['inventory_item_id'], 'InventoryItem'),
                                'locationId' => $this->ensureGid($settings['location_id'], 'Location'),
                                'delta' => (int)($settings['delta'] ?? 0),
                            ]
                        ]
                    ]
                ];

            case 'create_basic_discount':
                return [
                    'basicCodeDiscount' => [
                        'title' => $settings['title'],
                        'code' => $settings['code'],
                        'startsAt' => now()->toIso8601String(),
                        'customerSelection' => ['all' => true],
                        'customerGets' => [
                            'value' => [
                                ($settings['value_type'] === 'percentage' ? 'percentage' : 'discountAmount') => (float)$settings['value']
                            ],
                            'items' => ['all' => true]
                        ]
                    ]
                ];

            case 'update_variant_price':
                // Try to find Product ID
                $productId = $payload['admin_graphql_api_id'] ?? null;
                if (!$productId && isset($payload['product_id'])) {
                    $productId = $payload['product_id'];
                }
                // Fallback: if 'id' is present and looks like product, or just use it
                if (!$productId && isset($payload['id'])) {
                    $productId = $payload['id'];
                }

                return [
                    'productId' => $this->ensureGid($productId, 'Product'),
                    'variants' => [
                        [
                            'id' => $this->ensureGid($settings['variant_id'] ?? null, 'ProductVariant'),
                            'price' => (string)$settings['price']
                        ]
                    ]
                ];
        }

        return [];
    }

    protected function ensureGid($id, $type)
    {
        if (!$id) return null;
        if (strpos($id, 'gid://') === 0) return $id;
        return "gid://shopify/{$type}/{$id}";
    }

    protected function getQueryForMutation($name)
    {
        switch ($name) {
            case 'orderClose':
                return 'mutation orderClose($input: OrderCloseInput!) { orderClose(input: $input) { order { id } userErrors { field message } } }';
            case 'orderOpen':
                return 'mutation orderOpen($input: OrderOpenInput!) { orderOpen(input: $input) { order { id } userErrors { field message } } }';
            case 'productDelete':
                return 'mutation productDelete($input: ProductDeleteInput!) { productDelete(input: $input) { deletedProductId userErrors { field message } } }';
            case 'collectionAddProducts':
                return 'mutation collectionAddProducts($id: ID!, $productIds: [ID!]!) { collectionAddProducts(id: $id, productIds: $productIds) { collection { id } userErrors { field message } } }';
            case 'fulfillmentOrderHold':
                return 'mutation fulfillmentOrderHold($fulfillmentOrderId: ID!, $fulfillmentOrderLineItemsToHold: [FulfillmentOrderLineItemInput!]!, $reason: FulfillmentOrderHoldReason!, $reasonNotes: String) { fulfillmentOrderHold(fulfillmentOrderId: $fulfillmentOrderId, fulfillmentOrderLineItemsToHold: $fulfillmentOrderLineItemsToHold, reason: $reason, reasonNotes: $reasonNotes) { fulfillmentOrder { id } userErrors { field message } } }';
            case 'orderCancel':
                return 'mutation orderCancel($id: ID!, $reason: OrderCancelReason!, $note: String) { orderCancel(id: $id, reason: $reason, note: $note) { order { id } userErrors { field message } } }';
            case 'orderPaymentUpsert':
                return 'mutation orderPaymentUpsert($id: ID!) { orderPaymentUpsert(id: $id) { order { id } userErrors { field message } } }';
            case 'customerUpdate':
                return 'mutation customerUpdate($input: CustomerInput!) { customerUpdate(input: $input) { customer { id } userErrors { field message } } }';
            case 'inventoryAdjustQuantities':
                return 'mutation inventoryAdjustQuantities($input: InventoryAdjustQuantitiesInput!) { inventoryAdjustQuantities(input: $input) { inventoryAdjustmentGroup { id } userErrors { field message } } }';
            case 'discountCodeBasicCreate':
                return 'mutation discountCodeBasicCreate($basicCodeDiscount: DiscountCodeBasicInput!) { discountCodeBasicCreate(basicCodeDiscount: $basicCodeDiscount) { codeDiscountNode { idDiscount { id } } userErrors { field message } } }';
            case 'productVariantsBulkUpdate':
                return 'mutation productVariantsBulkUpdate($productId: ID!, $variants: [ProductVariantsBulkInput!]!) { productVariantsBulkUpdate(productId: $productId, variants: $variants) { productVariants { id } userErrors { field message } } }';
        }
        return '';
    }
}
