<?php

echo "Connectors: " . \App\Models\Connector::count() . "\n";
echo "Triggers: " . \App\Models\ConnectorTrigger::count() . "\n";
echo "Actions: " . \App\Models\ConnectorAction::count() . "\n";

$shopify = \App\Models\Connector::where('slug', 'shopify')->with('triggers', 'actions')->first();
if ($shopify) {
    echo "Shopify Triggers: " . $shopify->triggers->count() . "\n";
    echo "Shopify Actions: " . $shopify->actions->count() . "\n";
} else {
    echo "Shopify Connector not found.\n";
}
