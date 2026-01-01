<?php

use App\Services\N8NService;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new N8NService();
$nodes = $service->getNodeTypes();

foreach ($nodes as $node) {
    if ($node['name'] === 'n8n-nodes-base.shopify') {
        echo "Shopify Properties Count: " . count($node['properties'] ?? []) . "\n";
        // Print first 5 properties names
        if (!empty($node['properties'])) {
             foreach (array_slice($node['properties'], 0, 5) as $p) {
                 echo "- " . ($p['name'] ?? 'unnamed') . "\n";
             }
        }
        
        // Check for a known property that I DID NOT add, e.g. "authentication" (if n8n uses that) or "returnAll"
        // My NodeDefinitions has: resource, operation, title, body_html, vendor, product_type, tags.
        // If I see "returnAll" or "filters", it's from API.
    }
}
