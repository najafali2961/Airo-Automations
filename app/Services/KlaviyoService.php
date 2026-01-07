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
            'lists:write',
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
        $response = Http::asForm()
            ->withBasicAuth($this->clientId, $this->clientSecret)
            ->post($this->tokenUrl, [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirectUri,
                'code_verifier' => $codeVerifier
            ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch token: ' . $response->body());
        }

        return $response->json();
    }

    public function refreshAccessToken(KlaviyoCredential $credential)
    {
        $response = Http::asForm()
            ->withBasicAuth($this->clientId, $this->clientSecret)
            ->post($this->tokenUrl, [
                'grant_type' => 'refresh_token',
                'refresh_token' => $credential->refresh_token,
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
            'Revision' => '2024-02-15',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->baseUrl($this->baseUrl);
    }
    
    // --- Helper Methods for Actions ---

    /**
     * Get all lists (segments not included usually)
     */
    public function getLists($credential) {
        return $this->getClient($credential)->get('/api/lists');
    }

    /**
     * Get Profile ID by Email (Create or Update to ensure it exists)
     */
    public function getProfileIdByEmail($credential, $email) {
        // We try to create/update profile to get ID
        $response = $this->getClient($credential)->post('/api/profiles', [
            'data' => [
                'type' => 'profile',
                'attributes' => [
                    'email' => $email
                ]
            ]
        ]);

        if ($response->successful()) {
            return $response->json()['data']['id'];
        }

        // If 409, it means it exists, but the response might not return ID in V3 for native 409?
        // Klaviyo V3 returns 409 conflict if exists. The error *usually* contains the ID?
        // Or we should search for it.
        
        // Let's try searching if create fails
        $searchResponse = $this->getClient($credential)->get('/api/profiles', [
            'filter' => "equals(email,\"$email\")"
        ]);
        
        if ($searchResponse->successful()) {
            $data = $searchResponse->json()['data'];
            if (!empty($data)) {
                return $data[0]['id'];
            }
        }
        
        return null;
    }

    /**
     * Add profile to a list
     */
    public function addProfileToList($credential, $listId, $profileId) {
        // V3: POST /api/lists/{list_id}/relationships/profiles
        /*
          {
            "data": [
              { "type": "profile", "id": "PROFILE_ID" }
            ]
          }
        */
        return $this->getClient($credential)->post("/api/lists/{$listId}/relationships/profiles", [
            'data' => [
                ['type' => 'profile', 'id' => $profileId]
            ]
        ]);
    }

    /**
     * Track Event (Metric)
     */
    public function trackEvent($credential, $eventName, $profileProperties, $eventProperties) {
        /*
          V3: POST /api/events
          {
            "data": {
              "type": "event",
              "attributes": {
                "properties": { ... },
                "metric": {
                  "data": {
                    "type": "metric",
                    "attributes": { "name": "Placed Order" }
                  }
                },
                "profile": {
                  "data": {
                    "type": "profile",
                    "attributes": { "email": "..." }
                  }
                }
              }
            }
          }
        */
        
        $payload = [
            'data' => [
                'type' => 'event',
                'attributes' => [
                    'properties' => $eventProperties,
                    'metric' => [
                        'data' => [
                            'type' => 'metric',
                            'attributes' => [
                                'name' => $eventName
                            ]
                        ]
                    ],
                    'profile' => [
                        'data' => [
                            'type' => 'profile',
                            'attributes' => $profileProperties
                        ]
                    ]
                ]
            ]
        ];

        return $this->getClient($credential)->post('/api/events', $payload);
    }
}
