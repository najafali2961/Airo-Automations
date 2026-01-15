<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackService
{
    protected function resolveToken($auth)
    {
        // 1. If it's a string, assume it's a token
        if (is_string($auth)) {
            return $auth;
        }

        // 2. If it's a UserConnector
        if ($auth instanceof \App\Models\UserConnector) {
             return $auth->credentials['access_token'] ?? null;
        }

        // 3. If it's a User
        if ($auth instanceof \App\Models\User) {
            // Try new connector first
            $connector = $auth->activeConnectors()->where('connector_slug', 'slack')->first();
            if ($connector) {
                 return $connector->credentials['access_token'] ?? null;
            }
            // Fallback to legacy
            return $auth->slackCredential->access_token ?? null;
        }

        throw new \Exception("Invalid auth provided to SlackService.");
    }

    public function sendMessage($auth, $channel, $text)
    {
        $token = $this->resolveToken($auth);
        
        if (!$token) {
             throw new \Exception("Slack Not Connected (No Token Found).");
        }

        // channel often needs to be a channel ID, but Slack allows names for some bot contexts (though ID is safer).
        // Since we save 'channel_id' in credentials, we default to that if $channel is empty.
        
        $response = Http::withToken($token)->post('https://slack.com/api/chat.postMessage', [
            'channel' => $channel,
            'text' => $text,
            // 'unfurl_links' => true,
            // 'unfurl_media' => true,
        ]);

        if (!$response->successful()) {
            Log::error("Slack API Error: " . $response->body());
            throw new \Exception("Slack API Request Failed: " . $response->status());
        }

        $data = $response->json();
        
        if (!$data['ok']) {
            Log::error("Slack API Error (Business Logic): " . json_encode($data));
            throw new \Exception("Slack Message Failed: " . ($data['error'] ?? 'Unknown error'));
        }

        return $data;
    }
    public function getChannels($auth)
    {
        $token = $this->resolveToken($auth);

        if (!$token) {
             throw new \Exception("Slack Not Connected (No Token Found).");
        }

        // Fetch public and private channels
        $response = Http::withToken($token)->get('https://slack.com/api/conversations.list', [
            'types' => 'public_channel,private_channel',
            'limit' => 1000,
            'exclude_archived' => true
        ]);

        if (!$response->successful()) {
            Log::error("Slack API Error (Channels): " . $response->body());
            throw new \Exception("Slack API Request Failed: " . $response->status());
        }

        $data = $response->json();
        
        if (!$data['ok']) {
            Log::error("Slack API Error (Channels Logic): " . json_encode($data));
            throw new \Exception("Failed to fetch channels: " . ($data['error'] ?? 'Unknown error'));
        }

        return $data['channels'] ?? [];
    }
}
