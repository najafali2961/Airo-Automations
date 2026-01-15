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
        if ($user) {
            $user->activeConnectors()->where('connector_slug', 'google')->update([
                'is_active' => false,
                'credentials' => null,
                'expires_at' => null,
            ]);
        }
        }

        return redirect()->back()->with('success', 'Google account disconnected.');
    }

    public function generateAuthUrl(Request $request)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Generate a SIGNED url for the redirect endpoint
            // This URL will be valid for 60 seconds and includes the user ID
            $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'auth.google.redirect',
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
        // Protected by 'signed' middleware, so we trust the params
        try {
            $userId = $request->get('user_id');
            $user = \App\Models\User::find($userId);
            
            if (!$user) {
                \Log::error("GoogleAuth: Signed URL valid but user not found: $userId");
                return view('auth.popup-close', ['error' => 'User not found']);
            }

            // Create a state token with User ID and Host to persist context
            $statePayload = json_encode([
                'user_id' => $user->id,
                'shop' => $user->name,
                'host' => $request->get('host'), // Capture host for redirect back
                'nonce' => \Illuminate\Support\Str::random(16)
            ]);
            $state = base64_encode($statePayload);

            \Log::info("GoogleAuth: Redirecting user {$user->id} ({$user->name}) to Google with state.", ['state' => $state]);

            return redirect()->away($this->googleService->getAuthUrl($state));
        } catch (\Exception $e) {
            \Log::error("GoogleAuth: Redirect failed: " . $e->getMessage());
            return view('auth.popup-close', ['error' => 'Failed to initiate Google Auth']);
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

            $user->connectors()->updateOrCreate(
                ['connector_slug' => 'google'],
                [
                    'is_active' => true,
                    'credentials' => [
                        'access_token' => $token['access_token'],
                        // If refreshing, we might need old refresh token if new one not provided?
                        // Google usually provides refresh token only on first consent or forced prompt.
                        'refresh_token' => $token['refresh_token'] ?? $user->connectors()->where('connector_slug', 'google')->value('credentials')['refresh_token'] ?? null,
                    ],
                    'expires_at' => now()->addSeconds($token['expires_in']),
                ]
            );

            // Backwards compatibility: Keep writing to user table for now?? 
            // Better to remove it to force usage of new table. 
            // But let's nullify the old columns to avoid confusion? 
            // Actually, keep them in sync if we want a safe rollout, but user asked for "Universal System".
            // Let's stick to modifying the UserConnector only.
            
            // $user->update([
            //    'google_access_token' => $token['access_token'],
            //    ...
            // ]);
            
            \Log::info("GoogleAuth: User updated. Returning to popup close view.");
            
            return view('auth.popup-close');
        } catch (\Exception $e) {
            \Log::error("GoogleAuth: Callback failed: " . $e->getMessage());
            return redirect()->route('home')->with('error', 'Failed to connect Google account: ' . $e->getMessage());
        }
    }
}
