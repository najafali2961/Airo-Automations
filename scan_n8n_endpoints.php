<?php

use Illuminate\Support\Facades\Http;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiKey = env('N8N_API_KEY');
$baseUrl = rtrim(env('N8N_BASE_URL'), '/'); 

$endpoints = [
    '/api/v1/nodes',
    '/api/v1/node-descriptions',
    '/api/v1/packages',
    '/rest/node-types',
    '/rest/nodes',
    '/rest/node-descriptions',
    '/types/nodes.json'
];

foreach ($endpoints as $ep) {
    $url = $baseUrl . $ep;
    echo "Testing $url ... ";
    try {
        $response = Http::withHeaders([
            'X-N8N-API-KEY' => $apiKey,
        ])->get($url);
        
        echo $response->status();
        if ($response->successful()) {
            $data = $response->json();
            $count = is_array($data) ? count($data) : (isset($data['data']) ? count($data['data']) : 'object');
            echo " (Count/Type: $count)\n";
            // If it's nodes.json or similar, show first few keys
            if (str_contains($ep, 'nodes.json')) {
                 print_r(array_slice($data, 0, 1));
            }
        } else {
            echo " (" . substr($response->body(), 0, 50) . ")\n";
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
