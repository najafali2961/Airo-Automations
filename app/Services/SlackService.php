<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackService
{
    public function sendMessage($token, $channel, $text)
    {
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
}
