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
        // Debugging Config Loading
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

        $this->log("Found " . count($triggers) . " triggers and " . count($actions) . " actions in config.");

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
            $this->log("Connectors synced.");

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
            $this->log("Seeded $triggerCount triggers.");

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
            $this->log("Seeded $actionCount actions.");

            DB::commit();
            $this->log('Database seeding completed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->log('Failed to seed connectors: ' . $e->getMessage(), 'error');
            throw $e; // Re-throw to see error in browser
        }
    }
}
