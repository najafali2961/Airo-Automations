<?php

namespace App\Http\Controllers;

use App\Services\GoogleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoogleAuthController extends Controller
{
    protected $googleService;

    public function __construct(GoogleService $googleService)
    {
        $this->googleService = $googleService;
    }

    public function disconnect()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user) {
            $user->update([
                'google_access_token' => null,
                'google_refresh_token' => null,
                'google_token_expires_at' => null,
            ]);
        }

        return redirect()->back()->with('success', 'Google account disconnected.');
    }

    public function redirect(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                \Log::error("GoogleAuth: No authenticated user found for redirect.");
                return redirect()->route('home')->with('error', 'Authentication required.');
            }

            // Create a state token with User ID and Host to persist context
            $statePayload = json_encode([
                'user_id' => $user->id,
                'shop' => $user->name,
                'host' => $request->input('host'), // Capture host for redirect back
                'nonce' => \Illuminate\Support\Str::random(16)
            ]);
            $state = base64_encode($statePayload);

            \Log::info("GoogleAuth: Redirecting user {$user->id} ({$user->name}) to Google with state.", ['state' => $state]);

            return redirect()->away($this->googleService->getAuthUrl($state));
        } catch (\Exception $e) {
            \Log::error("GoogleAuth: Redirect failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to initiate Google Auth.');
        }
    }

    public function callback(Request $request)
    {
        \Log::info("GoogleAuth: Callback received.", $request->all());

        if (!$request->has('code')) {
            \Log::error("GoogleAuth: No code returned.");
            return redirect()->route('home')->with('error', 'Google authentication failed.');
        }

        try {
            // Restore User Context from State
            $state = $request->get('state');
            $user = null;

            if ($state) {
                $decoded = json_decode(base64_decode($state), true);
                if (isset($decoded['user_id'])) {
                    $user = \App\Models\User::find($decoded['user_id']);
                    \Log::info("GoogleAuth: Restored user context from state: {$decoded['user_id']}", ['found' => (bool)$user]);
                }
            }

            // Fallback to Auth::user() if state fails, but state is preferred
            if (!$user) {
                $user = Auth::user();
                \Log::warning("GoogleAuth: State missing or invalid. Falling back to Auth::user().", ['user_id' => $user?->id]);
            }

            if (!$user) {
                throw new \Exception("Could not identify user for Google Auth callback.");
            }

            // Force login to ensure session is active for the redirect back to generic app
            if (!Auth::check() || Auth::id() !== $user->id) {
                 \Log::info("GoogleAuth: Logging in user {$user->id}.");
                 Auth::login($user);
            }

            $token = $this->googleService->fetchAccessToken($request->get('code'));
            
            \Log::info("GoogleAuth: Token fetched successfully for user {$user->id}. Saving to DB.");

            $user->update([
                'google_access_token' => $token['access_token'],
                'google_refresh_token' => $token['refresh_token'] ?? $user->google_refresh_token,
                'google_token_expires_at' => now()->addSeconds($token['expires_in']),
            ]);
            
            \Log::info("GoogleAuth: User updated. Returning to popup close view.");
            
            return view('auth.popup-close');
        } catch (\Exception $e) {
            \Log::error("GoogleAuth: Callback failed: " . $e->getMessage());
            return redirect()->route('home')->with('error', 'Failed to connect Google account: ' . $e->getMessage());
        }
    }
}
