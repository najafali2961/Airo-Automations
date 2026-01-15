<?php

namespace App\Flow\Actions\Slack;

use App\Flow\Contracts\ActionInterface;
use App\Models\Node;
use App\Models\Execution;
use App\Services\SlackService;
use Illuminate\Support\Facades\Log;

class SendSlackMessageAction implements ActionInterface
{
    protected $variableService;

    public function __construct(SlackService $slackService, \App\Services\VariableService $variableService)
    {
        $this->slackService = $slackService;
        $this->variableService = $variableService;
    }

    public function handle(Node $node, array $payload, Execution $execution): void
    {
        Log::info("[SlackAction_v2.5] Handling Slack Action...");
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

        // 1. Resolve Auth & Default Channel
        $connector = $user->activeConnectors()->where('connector_slug', 'slack')->first();
        $defaultChannel = $connector->meta['default_channel_id'] ?? null;
        
        // Fallback to legacy
        if (!$connector) {
             $credential = $user->slackCredential;
             if ($credential) {
                  $defaultChannel = $credential->channel_id;
             }
        }
        
        // Check if we have minimal auth requirement (Connector OR Legacy Credential)
        // Note: We don't throw here strictly for auth, we let the Service throw if it can't resolve a token from $user.
        // But for channel logic, we need to know.

        $settings = $node->settings ?? [];
        $channel = $settings['channel'] ?? $defaultChannel;
        $rawMessage = $settings['message'] ?? '';
        
        if (empty($channel)) {
            throw new \Exception("No Slack Channel specified and no default channel found.");
        }
        
        if (empty($rawMessage)) {
            throw new \Exception("Slack Message cannot be empty.");
        }

        $message = $this->variableService->replace($rawMessage, $payload);

        try {
            // Pass $user to Service, it handles UserConnector vs Legacy resolution
            $this->slackService->sendMessage(
                $user,
                $channel,
                $message
            );
            
            Log::info("Slack Message sent to $channel");

        } catch (\Exception $e) {
            Log::error("Slack Action Error: " . $e->getMessage());
            throw $e;
        }
    }
}
