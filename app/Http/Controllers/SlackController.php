<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\SlackCredential;

class SlackController extends Controller
{
    public function redirect(Request $request)
    {
        $scopes = 'chat:write,chat:write.public,channels:read'; // Minimal scopes
        $redirectUri = config('services.slack.redirect');
        $clientId = config('services.slack.client_id');
        
        $url = "https://slack.com/oauth/v2/authorize?client_id={$clientId}&scope={$scopes}&redirect_uri={$redirectUri}";
        
        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        $code = $request->input('code');
        
        if (!$code) {
            return redirect()->route('home')->with('error', 'Slack authentication failed.');
        }

        try {
            $response = Http::asForm()->post('https://slack.com/api/oauth.v2.access', [
                'client_id' => config('services.slack.client_id'),
                'client_secret' => config('services.slack.client_secret'),
                'code' => $code,
                'redirect_uri' => config('services.slack.redirect'),
            ]);

            $data = $response->json();

            if (!$data['ok']) {
                Log::error('Slack Auth Failed', $data);
                return redirect()->route('home')->with('error', 'Failed to connect to Slack: ' . ($data['error'] ?? 'Unknown error'));
            }

            // Save Credentials
            $user = Auth::user(); // Assuming authenticated shopify user
            
            // If called from outside app context (e.g. browser), we might need to rely on session or state parameter.
            // But usually this starts from the generic app iframe.
            
            if (!$user) {
                 // Fallback if session is lost (happens in iframes sometimes)
                 Log::warning("Slack Callback: User not found in session.");
                 return redirect()->route('home')->with('error', 'Session expired. Please try again from the app.');
            }

            SlackCredential::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'access_token' => $data['access_token'],
                    'team_id' => $data['team']['id'],
                    'team_name' => $data['team']['name'],
                    'channel_id' => $data['incoming_webhook']['channel_id'] ?? null, // Sometimes returned if webhook scope used
                    'refresh_token' => null, // Not always providing refresh tokens unless requested
                ]
            );

            return redirect()->route('home')->with('success', 'Slack connected successfully!');

        } catch (\Exception $e) {
            Log::error('Slack Callback Exception: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'An error occurred connecting to Slack.');
        }
    }
}
