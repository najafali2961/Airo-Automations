<?php

namespace App\Flow\Actions\System;

use App\Flow\Actions\BaseAction;
use App\Models\Node;
use App\Models\Execution;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWebhook extends BaseAction
{
    public function handle(Node $node, array $payload, Execution $execution): void
    {
        $settings = $this->getSettings($node);
        $url = $settings['url'] ?? null;

        if (!$url) {
            $this->log($execution, $node->id, 'error', "Missing Webhook URL.");
            return;
        }

        $this->log($execution, $node->id, 'info', "Sending webhook to: {$url}");

        try {
            $response = Http::post($url, [
                'event' => $execution->event,
                'external_event_id' => $execution->external_event_id,
                'payload' => $payload,
                'timestamp' => now()->toIso8601String()
            ]);

            if ($response->successful()) {
                $this->log($execution, $node->id, 'info', "Webhook sent successfully. Status: " . $response->status());
            } else {
                $this->log($execution, $node->id, 'error', "Webhook failed. Status: " . $response->status() . " Body: " . $response->body());
            }
        } catch (\Throwable $e) {
            $this->log($execution, $node->id, 'error', "Failed to send webhook: " . $e->getMessage());
        }
    }
}
