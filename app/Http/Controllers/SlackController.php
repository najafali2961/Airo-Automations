<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\SlackCredential;

class SlackController extends Controller
{
    public function generateAuthUrl(Request $request)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Generate a SIGNED url for the redirect endpoint
            $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'slack.auth.redirect',
                now()->addMinutes(1),
                [
                    'user_id' => $user->id,
                    'host' => $request->input('host')
                ]
            );

            return response()->json(['url' => $url]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function redirect(Request $request)
    {
        // Protected by 'signed' middleware
        try {
            $userId = $request->get('user_id');
            $user = \App\Models\User::find($userId);
            
            if (!$user) {
                \Log::error("SlackAuth: User not found: $userId");
                return view('auth.popup-close', ['error' => 'User not found']);
            }

            // Create state token
            $statePayload = json_encode([
                'user_id' => $user->id,
                'shop' => $user->name,
                'host' => $request->get('host'), 
                'nonce' => \Illuminate\Support\Str::random(16)
            ]);
            $state = base64_encode($statePayload);

            $scopes = 'chat:write,chat:write.public,channels:read,groups:read';
            $redirectUri = config('services.slack.redirect');
            $clientId = config('services.slack.client_id');
            
            $url = "https://slack.com/oauth/v2/authorize?client_id={$clientId}&scope={$scopes}&redirect_uri={$redirectUri}&state={$state}";
            
            return redirect()->away($url);
        } catch (\Exception $e) {
            \Log::error("SlackAuth: Redirect failed: " . $e->getMessage());
            return view('auth.popup-close', ['error' => 'Failed to initiate Slack Auth']);
        }
    }

    public function callback(Request $request)
    {
        $code = $request->input('code');
        $state = $request->input('state');
        
        if (!$code) {
            return redirect()->route('home')->with('error', 'Slack authentication failed.');
        }

        try {
            // Restore User Context from State
            $user = null;
            if ($state) {
                $decoded = json_decode(base64_decode($state), true);
                 if (isset($decoded['user_id'])) {
                    $user = \App\Models\User::find($decoded['user_id']);
                }
            }

            if (!$user) $user = Auth::user();

            if (!$user) {
                 return redirect()->route('home')->with('error', 'Session expired.');
            }
            
            if (!Auth::check() || Auth::id() !== $user->id) {
                 Auth::login($user);
            }

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

            SlackCredential::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'access_token' => $data['access_token'],
                    'team_id' => $data['team']['id'],
                    'team_name' => $data['team']['name'],
                    'channel_id' => $data['incoming_webhook']['channel_id'] ?? null,
                    'refresh_token' => null,
                ]
            );

            return view('auth.popup-close', ['message' => 'slack_auth_success']);

        } catch (\Exception $e) {
            Log::error('Slack Callback Exception: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'An error occurred connecting to Slack.');
        }
    }
    public function disconnect(Request $request)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Optionally call Slack's auth.revoke API if you want to invalidate the token on their side too
            // but for now, just removing from our DB is enough to force re-auth flow.
            
            SlackCredential::where('user_id', $user->id)->delete();
            
            return redirect()->back()->with('success', 'Slack account disconnected.');
        } catch (\Exception $e) {
            Log::error('Slack Disconnect Failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to disconnect'], 500);
        }
    }
}
