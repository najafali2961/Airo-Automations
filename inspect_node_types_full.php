<?php

use Illuminate\Support\Facades\Http;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiKey = env('N8N_API_KEY');
$baseUrl = env('N8N_BASE_URL'); 
$url = rtrim($baseUrl, '/') . '/api/v1/node-types';

echo "Fetching all node types from: $url\n";

try {
    $response = Http::withHeaders([
        'X-N8N-API-KEY' => $apiKey,
    ])->get($url);

    if ($response->successful()) {
        $data = $response->json();
        $nodes = $data['data'] ?? $data;
        echo "Found " . count($nodes) . " nodes.\n";
        
        // Find Shopify explicitly
        foreach ($nodes as $node) {
            if (str_contains($node['name'], 'shopify')) {
                echo "\nFound Shopify Node:\n";
                print_r($node);
                
                // If it has no properties, check if there's a link or something else
                if (empty($node['properties'])) {
                    echo "Notice: No properties found in list response.\n";
                }
            }
        }
        
    } else {
        echo "Error: " . $response->status() . "\n";
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
