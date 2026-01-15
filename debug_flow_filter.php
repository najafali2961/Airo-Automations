<?php
use App\Models\Connector;

// 1. Check DB State
$google = Connector::where('slug', 'google')->first();
echo "Google DB Status: " . ($google ? ($google->is_active ? 'Active' : 'Inactive') : 'Not Found') . "\n";

$activeSystemConnectors = Connector::where('is_active', true)->pluck('slug')->toArray();
$activeSystemConnectors[] = 'shopify';

echo "Active Connectors: " . implode(', ', $activeSystemConnectors) . "\n";

// 2. Check Config logic
$flowConfig = config('flow') ?? ['triggers' => [], 'actions' => []];
$actions = $flowConfig['actions'];
$googleActions = array_filter($actions, fn($a) => ($a['app'] ?? 'shopify') === 'google');

echo "Google Actions found in config: " . count($googleActions) . "\n";

$apps = [];
foreach ($actions as $action) {
    $appName = $action['app'] ?? 'shopify';
    $appName = strtolower($appName);

    if (!in_array($appName, $activeSystemConnectors)) {
        echo "Skipping action for app: $appName\n";
        continue;
    }
    
    if (!isset($apps[$appName])) $apps[$appName] = 0;
    $apps[$appName]++;
}

echo "Apps that would be shown: " . implode(', ', array_keys($apps)) . "\n";
