<?php

namespace App\Flow\Actions\Slack;

use App\Flow\Contracts\ActionInterface;
use App\Models\Node;
use App\Models\Execution;
use App\Services\SlackService;
use Illuminate\Support\Facades\Log;

class SendSlackMessageAction implements ActionInterface
{
    protected $slackService;

    public function __construct(SlackService $slackService)
    {
        $this->slackService = $slackService;
    }

    public function handle(Node $node, array $payload, Execution $execution): void
    {
        Log::info("--- START SLACK ACTION ---");
        
        $user = $execution->flow->user;
        
        // Resilience: Fallback to manual ID lookup if relation missing (Copied from SMTP logic)
        if (!$user) {
             $shopId = $execution->flow->shop_id; 
             if ($shopId) {
                  $user = \App\Models\User::find($shopId);
                  if (!$user && method_exists(\App\Models\User::class, 'withTrashed')) {
                       $user = \App\Models\User::withTrashed()->find($shopId);
                  }
             }
        }

        if (!$user) {
             throw new \Exception("User not found for execution context.");
        }

        $credential = $user->slackCredential;

        if (!$credential || !$credential->access_token) {
             throw new \Exception("Slack Credentials not found. Please connect Slack in Connectors.");
        }

        $settings = $node->settings ?? [];
        
        // Default to the channel chosen during OAuth if available
        $channel = $settings['channel'] ?? $credential->channel_id;
        $rawMessage = $settings['message'] ?? '';
        
        if (empty($channel)) {
            throw new \Exception("No Slack Channel specified and no default channel found.");
        }
        
        if (empty($rawMessage)) {
            throw new \Exception("Slack Message cannot be empty.");
        }

        $message = $this->replaceVariables($rawMessage, $payload);

        try {
            $this->slackService->sendMessage(
                $credential->access_token,
                $channel,
                $message
            );
            
            Log::info("Slack Message sent to $channel");

        } catch (\Exception $e) {
            Log::error("Slack Action Error: " . $e->getMessage());
            throw $e;
        }
    }

    private function replaceVariables($text, $payload)
    {
        if (empty($text)) return '';
        
        $flattened = \Illuminate\Support\Arr::dot($payload);
        
        // Smart Aliasing: Map root keys to common resource prefixes
        // This allows {{ order.id }} to resolve even if payload is just { id: ... }
        $aliases = [];
        foreach ($flattened as $key => $value) {
            $aliases["order.$key"] = $value;
            $aliases["product.$key"] = $value;
            $aliases["customer.$key"] = $value;
        }
        $flattened = array_merge($flattened, $aliases);
        
        // Explicit Overrides (if needed)
        if (isset($payload['title'])) $flattened['product.title'] = $payload['title'];
        if (isset($payload['name'])) $flattened['order.name'] = $payload['name'];
        if (isset($payload['id'])) $flattened['id'] = $payload['id'];
        
        foreach ($flattened as $key => $value) {
            if (is_array($value) || is_object($value)) continue;
            if (is_bool($value)) $value = $value ? 'true' : 'false';
            
            $text = str_replace("{{ " . $key . " }}", (string)$value, $text);
            $text = str_replace("{{" . $key . "}}", (string)$value, $text);
        }
        return $text;
    }
}
