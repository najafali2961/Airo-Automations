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

    public function getClient($entity = null)
    {
        if (!$entity) {
            $entity = Auth::user();
        }

        if (!$entity) {
            throw new \Exception('No user or connector provided for Google Client.');
        }

        // Determine if we have a User or a UserConnector
        $connector = null;
        $user = null;

        if ($entity instanceof \App\Models\UserConnector) {
            $connector = $entity;
            $user = $connector->user;
        } elseif ($entity instanceof \App\Models\User) {
            $user = $entity;
            // Try to find the new connector first
            $connector = $user->activeConnectors()->where('connector_slug', 'google')->first();
        }

        // 1. Try Connector Auth
        if ($connector) {
            $creds = $connector->credentials; // This is an array casted by model
            $accessToken = $creds['access_token'] ?? null;
            $refreshToken = $creds['refresh_token'] ?? null;
            $expiresAt = $connector->expires_at; // Carbon or string

            if (!$accessToken) {
                // Check if maybe using legacy fields on connector? Unlikely if casted.
                 throw new \Exception('Google Connector found but missing access_token.');
            }

            $this->client->setAccessToken($accessToken);

            if ($this->client->isAccessTokenExpired()) {
                if (!$refreshToken) {
                     // Try to see if we can get a refresh token from somewhere else or if it's lost
                     throw new \Exception('Google token expired and no refresh token available in connector.');
                }

                $check = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                
                if (isset($check['error'])) {
                     throw new \Exception('Error refreshing token (Connector): ' . json_encode($check));
                }

                if (!isset($check['access_token'])) {
                    throw new \Exception('Failed to refresh token (Connector), missing access_token. Resp: ' . json_encode($check));
                }

                // Update Connector
                $creds['access_token'] = $check['access_token'];
                $creds['expires_in'] = $check['expires_in'];
                $creds['created'] = time(); // Google client expects this often
                
                $connector->credentials = $creds;
                $connector->expires_at = now()->addSeconds($check['expires_in']);
                $connector->save();
                
                // Update client with new token
                $this->client->setAccessToken($check['access_token']);
            }

            return $this->client;
        }

        // 2. Fallback: Legacy User Auth
        if ($user && $user->google_access_token) {
            $this->client->setAccessToken($user->google_access_token);

            if ($this->client->isAccessTokenExpired()) {
                if ($user->google_refresh_token) {
                    $check = $this->client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
                    
                    if (isset($check['error'])) {
                         throw new \Exception('Error refreshing token (Legacy): ' . json_encode($check));
                    }

                    if (!isset($check['access_token'])) {
                        throw new \Exception('Failed to refresh token (Legacy).');
                    }
                    
                    $user->update([
                        'google_access_token' => $check['access_token'],
                        'google_token_expires_at' => now()->addSeconds($check['expires_in']),
                    ]);
                } else {
                     throw new \Exception('Google token expired (Legacy) and no refresh token.');
                }
            }
            return $this->client;
        }

        throw new \Exception('User not connected to Google (No Connector or Legacy Token found).');
    }
    public function getFiles($user = null, $mimeType = null)
    {
        $client = $this->getClient($user);
        $service = new \Google\Service\Drive($client);
        
        $optParams = [
            'pageSize' => 100,
            'fields' => 'nextPageToken, files(id, name, mimeType)',
            'q' => "trashed = false",
        ];

        if ($mimeType) {
            if ($mimeType === 'folder') {
                $optParams['q'] .= " and mimeType = 'application/vnd.google-apps.folder'";
            } elseif ($mimeType === 'sheet') {
                 $optParams['q'] .= " and mimeType = 'application/vnd.google-apps.spreadsheet'";
            }
        }

        $results = $service->files->listFiles($optParams);

        if (count($results->getFiles()) == 0) {
            return [];
        }

        return $results->getFiles();
    }
}
