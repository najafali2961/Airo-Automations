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

    public function redirect()
    {
        return redirect()->away($this->googleService->getAuthUrl());
    }

    public function callback(Request $request)
    {
        if (!$request->has('code')) {
            return redirect()->route('home')->with('error', 'Google authentication failed.');
        }

        try {
            $token = $this->googleService->fetchAccessToken($request->get('code'));
            
            // Allow multiple users on the same shop to have their own google tokens? 
            // Or one per shop? The User model is the shop owner usually in current setup?
            // Actually config/shopify-app.php usually maps User to Shop.
            
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // If we decide to store purely in user table:
            $user->update([
                'google_access_token' => $token['access_token'],
                'google_refresh_token' => $token['refresh_token'] ?? $user->google_refresh_token, // Refresh token is only returned on first consent
                'google_token_expires_at' => now()->addSeconds($token['expires_in']),
            ]);

            return redirect()->route('home')->with('success', 'Google account connected successfully.');
        } catch (\Exception $e) {
            return redirect()->route('home')->with('error', 'Failed to connect Google account: ' . $e->getMessage());
        }
    }
}
