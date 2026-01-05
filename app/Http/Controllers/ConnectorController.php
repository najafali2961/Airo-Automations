<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ConnectorController extends Controller
{
    public function index()
    {
        $configConnectors = config('connectors');
        $user = Auth::user();
        
        $connectors = [];
        
        foreach ($configConnectors as $key => $config) {
            $isConnected = false;
            
            if ($config['auth_type'] === 'oauth' && isset($config['connected_check'])) {
                $field = $config['connected_check'];
                if ($user->$field) {
                    $isConnected = true;
                }
            }
            
            $connectors[] = [
                'key' => $key,
                'title' => $config['title'],
                'description' => $config['description'],
                'icon' => $config['icon'],
                'status' => $isConnected ? 'Connected' : 'Disconnected',
                'auth_type' => $config['auth_type'],
                'auth_url' => isset($config['auth_route']) ? route($config['auth_route']) : null,
                'is_active' => $config['is_active']
            ];
        }

        return Inertia::render('Connectors/Index', [
            'connectors' => $connectors
        ]);
    }
}
