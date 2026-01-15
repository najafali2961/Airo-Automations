<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Flow;
use App\Jobs\RunFlowJob;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Verify HMAC
        $hmac = $request->header('X-Shopify-Hmac-Sha256');
        $data = $request->getContent();
        
        // Use the secret from config (osiset/laravel-shopify default)
        $secret = config('shopify-app.api_secret'); 
        
        if (!$this->verifyHmac($data, $hmac, $secret)) {
            Log::warning('Webhook HMAC verification failed', ['hmac' => $hmac]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // 2. Extract Headers and Payload
        $topic = $request->header('X-Shopify-Topic');
        $eventId = $request->header('X-Shopify-Webhook-Id');
        $shopDomain = $request->header('X-Shopify-Shop-Domain');
        $payload = json_decode($data, true);

        if (!$topic || !$shopDomain) {
            return response()->json(['error' => 'Missing headers'], 400);
        }

        Log::info("Webhook Checked: $topic for $shopDomain", ['event_id' => $eventId]);

        // 3. Find Matching Active Flows
        // Shopify sends topics like 'orders/create' (REST) or 'ORDERS_CREATE' (GraphQL)
        // Our config uses uppercase with underscores.
        $normalizedTopic = strtoupper(str_replace(['/', '-'], '_', $topic));

        $userModel = config('auth.providers.users.model');
        $shop = $userModel::where('name', $shopDomain)->first();

        if (!$shop) {
             Log::warning("Shop not found for webhook: $shopDomain");
             return response()->json(['message' => 'Shop not found'], 200);
        }

        // Query Flows: Active, Belongs to Shop, Has Trigger with matching topic
        // We check both the incoming topic and the normalized version
        // DEBUGGING: Fetch all flows and filter in PHP to see what's in DB
        $allFlows = Flow::where('shop_id', $shop->id)->where('active', true)->with('nodes')->get();
        Log::info("Found " . $allFlows->count() . " active flows for shop " . $shop->id);
        
        $flows = $allFlows->filter(function($flow) use ($topic, $normalizedTopic) {
            $hasMatchingTrigger = false;
            foreach ($flow->nodes as $node) {
                if ($node->type === 'trigger') {
                    $nodeTopic = $node->settings['topic'] ?? 'NULL';
                    Log::info("Flow {$flow->id} Trigger Topic: " . json_encode($nodeTopic) . " (Expected: $topic or $normalizedTopic)");
                    
                    if ($nodeTopic === $topic || $nodeTopic === $normalizedTopic) {
                        $hasMatchingTrigger = true;
                    }
                }
            }
            return $hasMatchingTrigger;
        });

        if ($flows->isEmpty()) {
            Log::info("No active flows found for topic: $topic or $normalizedTopic");
            return response()->json(['message' => 'No active flows found for this topic'], 200);
        }

        // 4. Dispatch Jobs
        foreach ($flows as $flow) {
            RunFlowJob::dispatch($flow, (array)$payload, $topic, (string)$eventId);
            Log::info("Dispatched Flow {$flow->id} for event $eventId");
        }

        return response()->json(['message' => 'Processed', 'flows_triggered' => $flows->count()]);
    }

    private function verifyHmac($data, $hmac, $secret)
    {
        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, $secret, true));
        return hash_equals($hmac, $calculatedHmac);
    }
}
