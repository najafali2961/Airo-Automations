<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\N8NService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    protected $n8nService;

    public function __construct(N8NService $n8nService)
    {
        $this->n8nService = $n8nService;
    }

    public function handle(Request $request, string $type)
    {
        // Verify shop and HMAC (usually handled by middleware, but custom logical checks here)
        $shopDomain = $request->header('x-shopify-shop-domain');
        $payload = $request->all();

        // 1. Log the webhook event
        // We need to resolve shop_id from domain
        $shop = \App\Models\User::where('name', $shopDomain)->first();

        if (!$shop) {
            Log::error("Webhook received for unknown shop: $shopDomain");
            return response()->json(['message' => 'Shop not found'], 404);
        }

        $logId = DB::table('webhook_logs')->insertGetId([
            'shop_id' => $shop->id,
            'topic' => $type,
            'payload' => json_encode($payload),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Trigger N8N Workflow
        // In a real scenario, we'd lookup the specific workflow ID for this shop & topic.
        // For MVP, we might send to a generic webhook or lookup via N8NService.
        // Let's assume we just log it for now as "Processed by Laravel".
        // To actually forward, we'd do:
        // $response = $this->n8nService->client()->post("/webhook/$type", $payload);

        // Update log status
        DB::table('webhook_logs')->where('id', $logId)->update([
            'status' => 'processed', // or 'forwarded'
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Webhook received']);
    }
}
