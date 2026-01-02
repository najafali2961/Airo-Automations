<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache; // For simple real-time logging

class CreateProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $shopDomain;
    protected $data;
    protected $jobId;

    public function __construct($shopDomain, $data, $jobId)
    {
        $this->shopDomain = $shopDomain;
        $this->data = $data;
        $this->jobId = $jobId;
    }

    public function handle()
    {
        $this->logProgress("Starting product creation process...");

        try {
            // 1. Authenticate / Get Shop
            $this->logProgress("Authenticating with Shopify...");
            $userModel = config('auth.providers.users.model');
            $shop = $userModel::where('name', $this->shopDomain)->firstOrFail();
            
            // 2. Prepare Payload
            $this->logProgress("Preparing product data...");
            $payload = [
                'product' => [
                    'title' => $this->data['title'],
                    'body_html' => $this->data['description'],
                    'product_type' => $this->data['type'],
                    'vendor' => $this->data['vendor'],
                    'variants' => [
                        [
                            'price' => $this->data['price'],
                            'sku' => $this->data['sku'],
                            'inventory_management' => 'shopify', 
                            'inventory_quantity' => (int)($this->data['quantity'] ?? 0)
                        ]
                    ]
                ]
            ];

            // 3. Call Shopify API
            $this->logProgress("Sending request to Shopify API...");
            
            // Use Osiset/Laravel-Shopify API helper
            $response = $shop->api()->rest('POST', '/admin/api/2024-07/products.json', $payload);

            if ($response['errors']) {
                $this->logProgress("API Error: " . json_encode($response['body']));
                throw new \Exception("Shopify API Error");
            }

            $product = $response['body']['product'];
            $this->logProgress("Product created successfully! ID: " . $product['id']);
            $this->logProgress("DONE");

        } catch (\Exception $e) {
            $this->logProgress("Execution failed: " . $e->getMessage());
            Log::error("Product Creation Failed", ['error' => $e->getMessage()]);
        }
    }

    protected function logProgress($message)
    {
        // Append to a cache key for the frontend to poll
        // Key: job_logs_{jobId}
        $key = "job_logs_{$this->jobId}";
        $logs = Cache::get($key, []);
        $logs[] = [
            'timestamp' => now()->toIso8601String(),
            'message' => $message
        ];
        Cache::put($key, $logs, 600); // Keep logs for 10 mins
    }
}
