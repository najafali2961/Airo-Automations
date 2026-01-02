<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ProcessWebhookJob;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $hmac = $request->header('X-Shopify-Hmac-Sha256');
        $topic = $request->header('X-Shopify-Topic');
        $payload = $request->getContent();
        
        if (!$this->verifyHmac($payload, $hmac)) {
            Log::warning("Webhook HMAC verification failed for topic: $topic");
            return response('Unauthorized', 401);
        }
        
        Log::info("Webhook received: {$topic}");
        
        $data = json_decode($payload, true);
        
        ProcessWebhookJob::dispatch($topic, $data);
        
        return response('OK', 200);
    }
    
    private function verifyHmac($data, $hmac)
    {
        $secret = env('SHOPIFY_API_SECRET');
        
        $calculatedHmac = base64_encode(
            hash_hmac(
                'sha256',
                $data,
                $secret,
                true
            )
        );
        
        return hash_equals($calculatedHmac, $hmac);
    }
}
