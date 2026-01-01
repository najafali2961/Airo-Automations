<?php

use Illuminate\Support\Facades\Http;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiKey = env('N8N_API_KEY');
$baseUrl = env('N8N_BASE_URL'); 
$url = rtrim($baseUrl, '/') . '/api/v1/node-types/shopify';

echo "Fetching Details from: $url\n";

try {
    $response = Http::withHeaders([
        'X-N8N-API-KEY' => $apiKey,
    ])->get($url);

    if ($response->successful()) {
        $json = $response->json();
        // Check for properties
        if (isset($json['properties'])) {
            echo "SUCCESS: Found properties!\n";
            echo "Count: " . count($json['properties']) . "\n";
            print_r(array_slice($json['properties'], 0, 3));
        } else {
            echo "Partial success? Response OK but no 'properties'.\n";
            print_r($json);
        }
    } else {
        echo "Failed: " . $response->status() . "\n";
        echo $response->body() . "\n";
        
        // Try internal endpoint?
        $url2 = rtrim($baseUrl, '/') . '/rest/node-types/n8n-nodes-base.shopify';
        echo "\nRetrying internal: $url2\n";
        $resp2 = Http::withHeaders([
            'X-N8N-API-KEY' => $apiKey,
        ])->get($url2);
        
        if ($resp2->successful()) {
             $json = $resp2->json();
             if (isset($json['properties'])) {
                echo "SUCCESS (Internal): Found properties!\n";
                echo "Count: " . count($json['properties']) . "\n";
             } else {
                 echo "Internal OK but no properties.\n";
             }
        } else {
            echo "Internal Failed: " . $resp2->status() . "\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
