<?php

use App\Services\N8NService;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new N8NService();
echo "Fetching Shopify API Schema...\n";
$schema = $service->getCredentialSchema('shopifyApi');
if ($schema) {
    echo "Success! Found " . count($schema) . " keys.\n";
    print_r($schema);
} else {
    echo "Failed to fetch schema.\n";
}
