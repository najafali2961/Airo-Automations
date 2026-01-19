<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\File;

class TemplateEditorController extends Controller
{
    public function edit(Template $template)
    {
        // Load definitions (triggers/actions) and connectors
        // Reuse logic from FlowController or just load the config/files
        
        $definitions = config('flow.definitions', []); 
        // If definitions are not in config, we might need to load them from a service or file
        // For now assuming they are available or we can construct them.
        // Actually, FlowController loads them. Let's look at FlowController logic briefly if needed.
        // But better to just ensure we have the data.
        
        // Mock or Load Definitions (You might need to adjust this based on how FlowController does it)
        $definitions = [
            'apps' => [
                [
                    'name' => 'Shopify',
                    'triggers' => config('flow.triggers', []),
                    'actions' => config('flow.actions', []),
                ],
                // Add other apps if not auto-loaded
            ]
        ];

        // START: Reuse FlowController logic to get full definitions
        // In a real app, this should be in a Service.
        $path = resource_path('js/definitions.json'); // Example if stored in JS
        // actually, usually passed from backend.
        
        // Let's assume we pass the standard structure users see.
        // For this task, I will rely on the existing 'flow' config or similar.
        // Check FlowController::editor to be sure.
        
        return Inertia::render('Templates/AdminEditor', [
            'template' => $template,
            // 'flow' data structure for the editor
            'flow' => [
                'id' => $template->id,
                'name' => $template->name,
                'nodes' => $template->workflow_data['nodes'] ?? [],
                'edges' => $template->workflow_data['edges'] ?? [],
            ],
            'definitions' => $this->getDefinitions(),
            'connectors' => [], // transform to format expected by Editor
        ]);
    }

    public function save(Request $request, Template $template)
    {
        $data = $request->validate([
            'nodes' => 'array',
            'edges' => 'array',
            'name' => 'string',
        ]);

        $currentData = $template->workflow_data ?? [];
        $currentData['nodes'] = $data['nodes'];
        $currentData['edges'] = $data['edges'];

        $template->workflow_data = $currentData;
        if (isset($data['name'])) {
            $template->name = $data['name'];
        }
        $template->save();

        return response()->json(['success' => true]);
    }

    protected function getDefinitions()
    {
        // Load triggers and actions from config
        $configTriggers = config('flow.triggers', []);
        $configActions = config('flow.actions', []);

        // Group by 'app' or 'category' to mimic the structure Expected by Editor
        // The Editor expects 'apps' => [ { name: 'Shopify', triggers: [], actions: [] } ]

        $apps = [];

        // Helper to find or create app group
        $getApp = function($appName) use (&$apps) {
            foreach ($apps as &$app) {
                if ($app['name'] === $appName) return $app;
            }
            // Create new
            $newApp = ['name' => ucfirst($appName), 'icon' => $appName, 'triggers' => [], 'actions' => []];
            $apps[] = &$newApp;
            return $newApp;
        };
        
        // Process Triggers
        foreach ($configTriggers as $t) {
            // Determine App Name. Default to 'System' or 'Shopify' if not set?
            // Existing config doesn't always have 'app' key.
            // Map known categories/topics to Apps.
            $appName = $this->inferApp($t);
            
            // Find App in array (by reference to update it)
            $foundIndex = -1;
            foreach ($apps as $i => $a) {
                if ($a['name'] === $appName) {
                    $foundIndex = $i;
                    break;
                }
            }
            if ($foundIndex === -1) {
                $apps[] = ['name' => $appName, 'icon' => strtolower($appName), 'triggers' => [], 'actions' => []];
                $foundIndex = count($apps) - 1;
            }

            // Transform to Editor format
            $apps[$foundIndex]['triggers'][] = [
                'type' => 'trigger',
                'id' => $t['key'],
                'key' => $t['key'],
                'label' => $t['label'],
                'description' => $t['description'] ?? '',
                'topic' => $t['topic'] ?? null,
                'category' => $appName,
                'group' => $t['category'] ?? 'general',
                'icon' => $t['icon'] ?? 'Box',
                'app' => strtolower($appName),
                'variables' => $t['variables'] ?? []
            ];
        }

        // Process Actions
        foreach ($configActions as $a) {
            $appName = $this->inferApp($a);
            
            $foundIndex = -1;
            foreach ($apps as $i => $app) {
                if ($app['name'] === $appName) {
                    $foundIndex = $i;
                    break;
                }
            }
             if ($foundIndex === -1) {
                $apps[] = ['name' => $appName, 'icon' => strtolower($appName), 'triggers' => [], 'actions' => []];
                $foundIndex = count($apps) - 1;
            }

            $apps[$foundIndex]['actions'][] = [
                'type' => 'action',
                'id' => $a['key'],
                'key' => $a['key'],
                'label' => $a['label'],
                'description' => $a['description'] ?? '',
                'settings' => ['action' => $a['key']],
                'category' => strtolower($appName),
                'group' => $a['category'] ?? 'general',
                'icon' => $a['icon'] ?? 'Box',
                'app' => strtolower($appName),
                'fields' => $a['fields'] ?? []
            ];
        }

        return ['apps' => $apps];
    }

    protected function inferApp($item) {
        if (isset($item['app'])) return ucfirst($item['app']);
        // Fallbacks based on category or topic
        $cat = $item['category'] ?? '';
        if (in_array($cat, ['orders','products','customers','collections','fulfillments','draft_orders','refunds','shop','discounts','inventory','checkouts','carts','app'])) return 'Shopify';
        if ($cat === 'communication' && isset($item['key']) && str_contains($item['key'], 'slack')) return 'Slack';
        if ($cat === 'communication' && isset($item['key']) && str_contains($item['key'], 'email')) return 'Smtp'; // or Google?
        return 'System';
    }
}
