<?php

namespace App\Flow\Actions\System;

use App\Flow\Actions\BaseAction;
use App\Models\Node;
use App\Models\Execution;
use Illuminate\Support\Facades\Http;

class SendHttpRequest extends BaseAction
{
    public function handle(Node $node, array $payload, Execution $execution): void
    {
        $settings = $node->settings['form'] ?? $node->settings;
        
        $method = strtoupper($settings['method'] ?? 'POST');
        $url = $settings['url'] ?? null;
        $body = $settings['body'] ?? $payload; // Default to sending the full payload
        $headers = $settings['headers'] ?? [];

        if (!$url) {
            $this->log($execution, $node->id, 'error', "Missing URL for HTTP request.");
            return;
        }

        $this->log($execution, $node->id, 'info', "Sending {$method} request to {$url}");

        try {
            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->$method($url, $body);

            $this->log($execution, $node->id, 'info', "HTTP Request completed.", [
                'status' => $response->status(),
                'response_body' => $response->limit(200) // Log first 200 chars
            ]);
        } catch (\Throwable $e) {
            $this->log($execution, $node->id, 'error', "HTTP Request failed: " . $e->getMessage());
        }
    }
}
