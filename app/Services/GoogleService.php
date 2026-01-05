<?php

namespace App\Services;

use Google\Client;
use Google\Service\Gmail;
use Google\Service\Sheets;
use Google\Service\Docs;
use Illuminate\Support\Facades\Auth;

class GoogleService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $this->client->setRedirectUri(route('auth.google.callback')); // Using named route
        $this->client->setAccessType('offline'); // key for refresh token
        $this->client->setPrompt('consent'); // key for getting refresh token every time or valid check
        
        // Define scopes
        $this->client->addScope(Gmail::GMAIL_SEND);
        $this->client->addScope(Sheets::SPREADSHEETS);
        $this->client->addScope(Docs::DOCUMENTS);
        $this->client->addScope(\Google\Service\Drive::DRIVE);
        $this->client->addScope('email');
    }

    public function getAuthUrl($state = null)
    {
        if ($state) {
            $this->client->setState($state);
        }
        return $this->client->createAuthUrl();
    }

    public function fetchAccessToken($code)
    {
        return $this->client->fetchAccessTokenWithAuthCode($code);
    }

    public function getClient($user = null)
    {
        /** @var \App\Models\User $user */
        if (!$user) {
            $user = Auth::user();
        }
        
        if (!$user || !$user->google_access_token) {
            throw new \Exception('User not connected to Google.');
        }

        $this->client->setAccessToken($user->google_access_token);

        if ($this->client->isAccessTokenExpired()) {
            if ($user->google_refresh_token) {
                $check = $this->client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
                
                if (isset($check['error'])) {
                     throw new \Exception('Error refreshing token: ' . json_encode($check));
                }

                if (!isset($check['access_token'])) {
                    throw new \Exception('Failed to refresh token, response missing access_token. Response: ' . json_encode($check));
                }
                
                // Update user token
                $user->update([
                    'google_access_token' => $check['access_token'],
                    'google_token_expires_at' => now()->addSeconds($check['expires_in']),
                ]);
            } else {
                 throw new \Exception('Google token expired and no refresh token available.');
            }
        }

        return $this->client;
    }
}
