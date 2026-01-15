<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ConnectorController extends Controller
{
    public function index()
    {
        // 1. Get all active connectors from Upgrade/Admin (Connectors Table)
        // Assuming we have a Connector model populated via Seeder/Admin
        $availableConnectors = \App\Models\Connector::where('is_active', true)->get();
        
        // 2. Get user's active connections
        $user = Auth::user();
        $userConnections = $user->activeConnectors()->pluck('connector_slug')->toArray();

        $connectors = [];

        foreach ($availableConnectors as $connector) {
            $isConnected = in_array($connector->slug, $userConnections);

            // Determine Auth Route dynamically based on slug
            // e.g. 'google' -> 'auth.google.url'
            // e.g. 'slack' -> 'slack.auth.url'
            $authRouteName = match($connector->slug) {
                'google' => 'auth.google.url',
                'slack' => 'slack.auth.url',
                'klaviyo' => 'klaviyo.auth.url',
                'smtp' => 'smtp.show', // SMTP is a config modal, not OAuth
                default => null
            };

            $connectors[] = [
                'key' => $connector->slug,
                'title' => $connector->name,
                'description' => $connector->description,
                'icon' => $connector->icon, // URL or class
                'status' => $isConnected ? 'Connected' : 'Disconnected',
                'auth_type' => $connector->slug === 'smtp' ? 'basic' : 'oauth', // Simplified logic for now
                'auth_url' => $authRouteName ? route($authRouteName) : null,
                'is_active' => true // It's in the list, so it's active
            ];
        }

        return Inertia::render('Connectors/Index', [
            'connectors' => $connectors
        ]);
    }
}
