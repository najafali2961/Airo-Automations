<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\KlaviyoCredential;

class KlaviyoService
{
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $baseUrl = 'https://a.klaviyo.com';
    protected $authUrl = 'https://www.klaviyo.com/oauth/authorize';
    protected $tokenUrl = 'https://a.klaviyo.com/oauth/token';

    public function __construct()
    {
        $this->clientId = config('services.klaviyo.client_id');
        $this->clientSecret = config('services.klaviyo.client_secret');
        $this->redirectUri = config('services.klaviyo.redirect');
    }

    public function generatePkce()
    {
        $verifier = \Illuminate\Support\Str::random(128);
        $challenge =  rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
        
        return [
            'verifier' => $verifier,
            'challenge' => $challenge
        ];
    }

    public function getAuthUrl($state, $codeChallenge)
    {
        $scopes = [
            'accounts:read',
            'lists:read',
            'profiles:read',
            'profiles:write',
            'events:write' 
        ];

        $query = http_build_query([
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
            'scope' => implode(' ', $scopes),
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256'
        ]);
        
        return "{$this->authUrl}?{$query}";
    }

    public function fetchAccessToken($code, $codeVerifier)
    {
        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code_verifier' => $codeVerifier
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch token: ' . $response->body());
        }

        return $response->json();
    }

    public function refreshAccessToken(KlaviyoCredential $credential)
    {
        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type' => 'refresh_token',
            'refresh_token' => $credential->refresh_token,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to refresh token: ' . $response->body());
        }

        $data = $response->json();
        
        $credential->update([
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'expires_at' => now()->addSeconds($data['expires_in']),
        ]);

        return $data['access_token'];
    }

    public function getClient(KlaviyoCredential $credential)
    {
        if ($credential->expires_at && $credential->expires_at->isPast()) {
            $this->refreshAccessToken($credential);
        }

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $credential->access_token,
            'Revision' => '2024-02-15' // Use a recent API revision
        ])->baseUrl($this->baseUrl);
    }
}
