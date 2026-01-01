<?php

use Illuminate\Support\Facades\Http;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiKey = env('N8N_API_KEY');
$url = 'https://primary-production-d49a.up.railway.app/types/nodes.json';

echo "Fetching $url with API Key...\n";

try {
    $response = Http::withHeaders([
        'X-N8N-API-KEY' => $apiKey,
    ])->get($url);

    if ($response->successful()) {
        echo "Success!\n";
        $data = $response->json();
        // Print first node as sample
        if (is_array($data) && count($data) > 0) {
            $first = array_shift($data);
            print_r($first);
            echo "\nTotal Nodes: " . (count($data) + 1) . "\n";
        } else {
            echo "Response is empty or not an array.\n";
            print_r($data);
        }
    } else {
        echo "Error: " . $response->status() . "\n";
        echo $response->body() . "\n";
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
