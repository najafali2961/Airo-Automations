<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
// use App\Models\KlaviyoCredential; // Removed legacy import
use App\Services\KlaviyoService;

class KlaviyoController extends Controller
{
    protected $klaviyoService;

    public function __construct(KlaviyoService $klaviyoService)
    {
        $this->klaviyoService = $klaviyoService;
    }

    public function generateAuthUrl(Request $request)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Generate a SIGNED url for the redirect endpoint
            $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'klaviyo.auth.redirect',
                now()->addMinutes(5),
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
                Log::error("KlaviyoAuth: User not found: $userId");
                return view('auth.popup-close', ['error' => 'User not found']);
            }

            // Generate PKCE
            $pkce = $this->klaviyoService->generatePkce();
            $verifier = $pkce['verifier'];
            $challenge = $pkce['challenge'];

            // Store Verifier in Cache identifying by user (State will carry user_id)
            // Cache for 10 minutes
            Cache::put('klaviyo_verifier_' . $user->id, $verifier, 600);

            // Create state token
            $statePayload = json_encode([
                'user_id' => $user->id,
                'shop' => $user->name,
                'host' => $request->get('host'), 
                'nonce' => \Illuminate\Support\Str::random(16)
            ]);
            $state = base64_encode($statePayload);

            $url = $this->klaviyoService->getAuthUrl($state, $challenge);
            
            return redirect()->away($url);
        } catch (\Exception $e) {
            Log::error("KlaviyoAuth: Redirect failed: " . $e->getMessage());
            return view('auth.popup-close', ['error' => 'Failed to initiate Klaviyo Auth']);
        }
    }

    public function callback(Request $request)
    {
        $code = $request->input('code');
        $state = $request->input('state');
        
        if (!$code) {
            return redirect()->route('home')->with('error', 'Klaviyo authentication failed.');
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

            // Retrieve Verifier
            $verifier = Cache::get('klaviyo_verifier_' . $user->id);
            if (!$verifier) {
                throw new \Exception('PKCE Verifier missing or expired.');
            }

            $data = $this->klaviyoService->fetchAccessToken($code, $verifier);
            
            // Clean up cache
            Cache::forget('klaviyo_verifier_' . $user->id);

            // Decode Access Token to get public key if available, or fetch it? 
            // The token response usually contains 'scope', 'expires_in', 'access_token', 'refresh_token'.
            // Klaviyo doesn't always return the Public Key / Account ID in the token response.
            // We might need to make a "Me" call to get the Account ID (Public Key).
            // For now, let's just store the tokens.

            $user->activeConnectors()->updateOrCreate(
                ['connector_slug' => 'klaviyo'],
                [
                    'is_active' => true,
                    'credentials' => [
                        'access_token' => $data['access_token'],
                        'refresh_token' => $data['refresh_token'],
                        'public_key' => $data['public_key'] ?? null, 
                    ],
                    'expires_at' => now()->addSeconds($data['expires_in']),
                ]
            );

            // Optional: Fetch Account ID to store as 'public_key'
            // $account = $this->klaviyoService->getAccount($token); ...

            return view('auth.popup-close', ['message' => 'klaviyo_auth_success']);

        } catch (\Exception $e) {
            Log::error('Klaviyo Callback Exception: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'An error occurred connecting to Klaviyo.');
        }
    }

    public function disconnect(Request $request)
    {
        try {
            $user = Auth::user();
            $user->activeConnectors()->where('connector_slug', 'klaviyo')->delete();
            return redirect()->back()->with('success', 'Klaviyo disconnected successfully.');
        } catch (\Exception $e) {
            Log::error('Klaviyo Disconnect Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to disconnect: ' . $e->getMessage());
        }
    }
}
