<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SlackService;
use App\Services\KlaviyoService;
use App\Services\GoogleService;
use Illuminate\Support\Facades\Auth;

class IntegrationResourceController extends Controller
{
    protected $slack;
    protected $klaviyo;
    protected $google;

    public function __construct(SlackService $slack, KlaviyoService $klaviyo, GoogleService $google)
    {
        $this->slack = $slack;
        $this->klaviyo = $klaviyo;
        $this->google = $google;
    }

    public function index(Request $request, $service, $resource)
    {
        $user = Auth::user();

        try {
            \Illuminate\Support\Facades\Log::info("IntegrationResourceController: Request received", [
                'service' => $service,
                'resource' => $resource,
                'user_id' => $user->id ?? 'auth_failed'
            ]);

            switch ($service) {
                case 'slack':
                    return $this->handleSlack($user, $resource);
                case 'klaviyo':
                    return $this->handleKlaviyo($user, $resource);
                case 'google':
                    return $this->handleGoogle($user, $resource);
                default:
                    \Illuminate\Support\Facades\Log::warning("IntegrationResourceController: Unknown service: $service");
                    return response()->json(['error' => 'Unknown service: ' . $service], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function handleSlack($user, $resource)
    {
        if ($resource === 'channels') {
            // Service handles resolution
            $channels = $this->slack->getChannels($user);
            
            return collect($channels)->map(function ($channel) {
                return [
                    'label' => '#' . $channel['name'],
                    'value' => $channel['id']
                ];
            })->values();
        }

        return response()->json(['error' => 'Unknown Slack resource'], 400);
    }

    protected function handleKlaviyo($user, $resource)
    {
        if ($resource === 'lists') {
            // Service handles resolution
            $response = $this->klaviyo->getLists($user);
            
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch from Klaviyo: ' . $response->body());
            }

            $data = $response->json();
            // Klaviyo V3 structure: { data: [ { id, attributes: { name } } ] }
            
            return collect($data['data'] ?? [])->map(function ($list) {
                return [
                    'label' => $list['attributes']['name'] ?? $list['id'],
                    'value' => $list['id']
                ];
            })->values();
        }

        return response()->json(['error' => 'Unknown Klaviyo resource'], 400);
    }

    protected function handleGoogle($user, $resource)
    {
        // Check if connected (Delegated to Service which handles UserConnector vs Legacy)
        // if (!$user->google_access_token) { ... } REMOVED


        if ($resource === 'drive_folders') {
            $files = $this->google->getFiles($user, 'folder');
            return collect($files)->map(function ($file) {
                return [
                    'label' => $file->getName(),
                    'value' => $file->getId()
                ];
            })->values();
        }

        if ($resource === 'google_sheets') {
             $files = $this->google->getFiles($user, 'sheet');
             return collect($files)->map(function ($file) {
                 return [
                     'label' => $file->getName(),
                     'value' => $file->getId()
                 ];
             })->values();
        }

        return response()->json(['error' => 'Unknown Google resource'], 400);
    }
}
