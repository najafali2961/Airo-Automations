<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Connector;
use App\Models\ConnectorTrigger;
use App\Models\ConnectorAction;
use Illuminate\Support\Facades\DB;

class ConnectorsSeeder extends Seeder
{
    private function log($message, $type = 'info')
    {
        if (isset($this->command)) {
            $this->command->$type($message);
        } else {
            \Log::$type("Seeder: " . $message);
        }
    }

    public function run()
    {
$config = config('flow');
        
        if (empty($config) || empty($config['triggers'])) {
            $this->log("Config 'flow' seems empty using config() helper. Attempting direct file load...", 'warn');
            $path = config_path('flow.php');
            if (file_exists($path)) {
                $config = require $path;
                $this->log("Loaded config directly from: $path");
            } else {
                $this->log("Config file not found at: $path", 'error');
                return;
            }
        }

        $triggers = $config['triggers'] ?? [];
        $actions = $config['actions'] ?? [];

       

        // Connectors to ensure exist
        $definedConnectors = [
            'shopify' => ['name' => 'Shopify', 'icon' => 'https://cdn-icons-png.flaticon.com/512/2504/2504914.png'],
            'google' => ['name' => 'Google', 'icon' => 'https://cdn-icons-png.flaticon.com/512/2991/2991148.png'],
            'slack' => ['name' => 'Slack', 'icon' => 'https://cdn-icons-png.flaticon.com/512/2111/2111615.png'],
            'smtp' => ['name' => 'SMTP', 'icon' => 'https://cdn-icons-png.flaticon.com/512/732/732200.png'],
            'klaviyo' => ['name' => 'Klaviyo', 'icon' => 'https://www.klaviyo.com/application-assets/klaviyo/production/static-assets/favicon.png'],
            'twilio' => ['name' => 'Twilio', 'icon' => 'https://cdn-icons-png.flaticon.com/512/5968/5968841.png'],
        ];

        DB::beginTransaction();
        try {
            // A. Create/Update Connectors
            foreach ($definedConnectors as $slug => $data) {
                Connector::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $data['name'],
                        'icon' => $data['icon'], 
                        'is_active' => true 
                    ]
                );
            }
          
            $explicitTriggers = [
                'products_update' => [
                    'label' => 'Product Updated',
                    'description' => 'Starts when a product is updated',
                    'topic' => 'PRODUCTS_UPDATE',
                    'category' => 'products',
                    'icon' => 'Package',
                    'variables' => [
                        ['label' => 'Product Title', 'value' => 'product.title'],
                        ['label' => 'Product ID', 'value' => 'product.id'],
                        ['label' => 'Product Handle', 'value' => 'product.handle'],
                        ['label' => 'Product Type', 'value' => 'product.product_type'],
                        ['label' => 'Vendor', 'value' => 'product.vendor'],
                        ['label' => 'Status', 'value' => 'product.status'],
                        ['label' => 'Tags', 'value' => 'product.tags'],
                    ]
                ],
                'products_create' => [
                    'label' => 'Product Created',
                    'description' => 'Starts when a product is created',
                    'topic' => 'PRODUCTS_CREATE',
                    'category' => 'products',
                    'icon' => 'Package',
                    'variables' => [
                        ['label' => 'Product Title', 'value' => 'product.title'],
                        ['label' => 'Product ID', 'value' => 'product.id'],
                        ['label' => 'Product Handle', 'value' => 'product.handle'],
                        ['label' => 'Product Type', 'value' => 'product.product_type'],
                        ['label' => 'Vendor', 'value' => 'product.vendor'],
                        ['label' => 'Status', 'value' => 'product.status'],
                    ]
                ],
                'orders_updated' => [
                    'label' => 'Order Updated',
                    'description' => 'Starts when an order is updated',
                    'topic' => 'ORDERS_UPDATED',
                    'category' => 'orders',
                    'icon' => 'ShoppingBag',
                    'variables' => [] // Ensure no bleed from create
                ],
                'products_delete' => [
                    'label' => 'Product Deleted',
                    'description' => 'Starts when a product is removed',
                    'topic' => 'PRODUCTS_DELETE',
                    'category' => 'products',
                    'icon' => 'Package',
                    'variables' => []
                ],
                // FIXED: Customer Triggers
                'customers_create' => [
                    'label' => 'Customer Created',
                    'description' => 'Starts when a customer is created',
                    'topic' => 'CUSTOMERS_CREATE',
                    'category' => 'customers',
                    'icon' => 'Users',
                    'variables' => [
                        ['label' => 'First Name', 'value' => 'customer.first_name'],
                        ['label' => 'Last Name', 'value' => 'customer.last_name'],
                        ['label' => 'Email', 'value' => 'customer.email'],
                        ['label' => 'Customer ID', 'value' => 'customer.id'],
                        ['label' => 'Phone', 'value' => 'customer.phone'],
                        ['label' => 'Total Spent', 'value' => 'customer.total_spent'],
                        ['label' => 'Orders Count', 'value' => 'customer.orders_count'],
                        ['label' => 'State', 'value' => 'customer.state'],
                        ['label' => 'Tags', 'value' => 'customer.tags'],
                    ]
                ],
                'customers_update' => [
                    'label' => 'Customer Updated',
                    'description' => 'Starts when a customer is updated',
                    'topic' => 'CUSTOMERS_UPDATE',
                    'category' => 'customers',
                    'icon' => 'Users',
                    'variables' => []
                ],
                'customers_delete' => [
                    'label' => 'Customer Deleted',
                    'description' => 'Starts when a customer is removed',
                    'topic' => 'CUSTOMERS_DELETE',
                    'category' => 'customers',
                    'icon' => 'Users',
                    'variables' => []
                ]
            ];

            // Merge explicit triggers into the config triggers list
            foreach ($explicitTriggers as $key => $data) {
                // Find existing entry index
                $found = false;
                foreach ($triggers as $idx => $t) {
                    if ($t['key'] === $key) {
                        $triggers[$idx] = array_merge($t, $data); 
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $data['key'] = $key;
                    $data['app'] = 'shopify'; 
                    $triggers[] = $data;
                }
            }

            // B. Seed Triggers
            $triggerCount = 0;
            foreach ($triggers as $trigger) {
                $appSlug = strtolower($trigger['app'] ?? 'shopify');
                $connector = Connector::where('slug', $appSlug)->first();
                
                if (!$connector) {
                    $this->log("Skipping trigger {$trigger['key']} - Connector $appSlug not found.", 'warn');
                    continue;
                }


                ConnectorTrigger::updateOrCreate(
                    [
                        'connector_id' => $connector->id,
                        'key' => $trigger['key']
                    ],
                    [
                        'label' => $trigger['label'],
                        'description' => $trigger['description'] ?? null,
                        'topic' => $trigger['topic'] ?? null,
                        'type' => 'trigger',
                        'category' => $trigger['category'] ?? 'general',
                        'icon' => $trigger['icon'] ?? 'Zap',
                        'variables' => $trigger['variables'] ?? [],
                        'is_active' => true
                    ]
                );
                $triggerCount++;
            }


            // Verification Log
       
            $verifyKeys = ['products_update', 'products_delete', 'customers_create', 'customers_delete'];
            $results = ConnectorTrigger::whereIn('key', $verifyKeys)->get();
           
            

            // B. Seed Triggers
            $triggerCount = 0;
            foreach ($triggers as $trigger) {
                $appSlug = strtolower($trigger['app'] ?? 'shopify');
                $connector = Connector::where('slug', $appSlug)->first();
                
                if (!$connector) {
                    $this->log("Skipping trigger {$trigger['key']} - Connector $appSlug not found.", 'warn');
                    continue;
                }

                ConnectorTrigger::updateOrCreate(
                    [
                        'connector_id' => $connector->id,
                        'key' => $trigger['key']
                    ],
                    [
                        'label' => $trigger['label'],
                        'description' => $trigger['description'] ?? null,
                        'topic' => $trigger['topic'] ?? null,
                        'type' => 'trigger',
                        'category' => $trigger['category'] ?? 'general',
                        'icon' => $trigger['icon'] ?? 'Zap',
                        'variables' => $trigger['variables'] ?? [],
                        'is_active' => true
                    ]
                );
                $triggerCount++;
            }
          
            // C. Seed Actions
            $actionCount = 0;
            foreach ($actions as $action) {
                $appSlug = strtolower($action['app'] ?? 'shopify');
                $connector = Connector::where('slug', $appSlug)->first();

                if (!$connector) {
                    $this->log("Skipping action {$action['key']} - Connector $appSlug not found.", 'warn');
                    continue;
                }

                ConnectorAction::updateOrCreate(
                    [
                        'connector_id' => $connector->id,
                        'key' => $action['key']
                    ],
                    [
                        'label' => $action['label'],
                        'description' => $action['description'] ?? null,
                        'category' => $action['category'] ?? 'general',
                        'icon' => $action['icon'] ?? 'Zap',
                        'fields' => $action['fields'] ?? [],
                        'topic' => $action['topic'] ?? null,
                        'is_active' => true
                    ]
                );
                $actionCount++;
            }


            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->log('Failed to seed connectors: ' . $e->getMessage(), 'error');
            throw $e; // Re-throw to see error in browser
        }
    }
}
