<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Osiset\ShopifyApp\Contracts\ShopModel;

class AppInstalledJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $shopDomain;

    public function __construct(ShopModel $shopDomain)
    {
        $this->shopDomain = $shopDomain;
    }

    public function handle()
    {
        // Logic to run after app is installed
        // For example:
        // 1. Register webhooks via API (if not handled by config)
        // 2. Create N8N Project via N8NService
        // 3. Send welcome email

        // Example: $this->shopDomain->api()->rest('POST', '/admin/api/.../webhooks.json', [...]);
    }
}
