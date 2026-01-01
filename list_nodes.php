<?php

use App\Services\N8NService;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new N8NService();
$nodes = $service->getNodeTypes();

echo "Found " . count($nodes) . " nodes.\n";
foreach ($nodes as $node) {
    echo $node['name'] . "\n";
}
