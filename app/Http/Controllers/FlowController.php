<?php

namespace App\Http\Controllers;

use App\Models\Flow;
use App\Models\Node;
use App\Models\Edge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FlowController extends Controller
{
    public function index() {
        return Inertia::render('Workflows/Index', [
             'flows' => auth()->user()->flows()
                ->withCount(['executions as execution_count'])
                ->orderByDesc('updated_at')
                ->get()
        ]);
    }

    public function editor($id = null) {
         $flow = $id ? auth()->user()->flows()->with(['nodes', 'edges'])->find($id) : null;
         
         if ($id && !$flow) abort(404);

         // Load from new flow.php config
         $flowConfig = config('flow');

         $shopifyTriggers = [];
         foreach ($flowConfig['triggers'] as $trigger) {
             $shopifyTriggers[] = [
                 'type' => 'trigger',
                 'n8nType' => 'shopifyTrigger',
                 'label' => $trigger['label'],
                 'description' => $trigger['description'],
                 'settings' => ['topic' => $trigger['topic']],
                 'group' => $trigger['category'],
                 'icon' => $trigger['icon']
             ];
         }

         $apps = [];
         
         // 1. Initialize Shopify App (as it has triggers too)
         $apps['shopify'] = [
             'name' => 'Shopify',
             'icon' => 'shopify',
             'triggers' => $shopifyTriggers,
             'actions' => []
         ];

         // 2. Group Actions dynamically
         foreach ($flowConfig['actions'] as $action) {
             $actionDef = [
                 'type' => 'action',
                 'n8nType' => 'shopifyAction',
                 'label' => $action['label'],
                 'description' => $action['description'],
                 'settings' => [
                     'action' => $action['key'],
                 ],
                 'group' => $action['category'],
                 'icon' => $action['icon'],
                 'fields' => $action['fields'] ?? []
             ];

             $appName = $action['app'] ?? 'shopify';
             $appName = strtolower($appName);

             if (!isset($apps[$appName])) {
                 $apps[$appName] = [
                     'name' => ucfirst($appName), // 'google' -> 'Google'
                     'icon' => $appName,
                     'triggers' => [],
                     'actions' => []
                 ];
             }

             $apps[$appName]['actions'][] = $actionDef;
         }
         
         // 3. Format for Frontend
         // Ensure Shopify is first if desired, or just array_values
         // We might want to enforce case for specific known apps if ucfirst isn't enough (e.g. SMTP)
         
         $formattedApps = [];
         foreach ($apps as $key => $appData) {
             if ($key === 'smtp') $appData['name'] = 'SMTP';
             // if ($key === 'shopify') $appData['name'] = 'Shopify'; // Already set
             
             $formattedApps[] = $appData;
         }
         
         $definitions = [
             'apps' => $formattedApps
         ];

         return Inertia::render('Workflows/Editor', [
             'flow' => $flow,
             'definitions' => $definitions
         ]);
    }

    public function save(Request $request) {
        $validated = $request->validate([
            'id' => 'nullable|exists:flows,id',
            'name' => 'required|string',
            'nodes' => 'required|array',
            'edges' => 'required|array'
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $user = auth()->user();
            $flow = null;
            
            if (!empty($validated['id'])) {
                $flow = $user->flows()->findOrFail($validated['id']);
                $flow->update(['name' => $validated['name']]);
                
                // Reset nodes/edges
                $flow->edges()->delete();
                $flow->nodes()->delete(); 
            } else {
                $flow = $user->flows()->create([
                    'name' => $validated['name'],
                    'active' => false
                ]);
            }

            // Save nodes
            $nodeMap = [];

            foreach ($validated['nodes'] as $nodeData) {
                // Determine label if not set
                $label = $nodeData['data']['label'] ?? $nodeData['type'];
                
                $node = $flow->nodes()->create([
                    'type' => $nodeData['type'],
                    'settings' => $nodeData['data']['settings'] ?? [],
                    'label' => $label,
                    'position_x' => $nodeData['position']['x'],
                    'position_y' => $nodeData['position']['y']
                ]);
                $nodeMap[$nodeData['id']] = $node->id;
            }

            // Save edges
            $savedEdges = [];
            foreach ($validated['edges'] as $edgeData) {
                if (isset($nodeMap[$edgeData['source']]) && isset($nodeMap[$edgeData['target']])) {
                    $sourceId = $nodeMap[$edgeData['source']];
                    $targetId = $nodeMap[$edgeData['target']];
                    $label = $edgeData['label'] ?? $edgeData['sourceHandle'] ?? 'then';

                    // Prevent duplicate edges (same source, target, and label)
                    $edgeKey = "{$sourceId}-{$targetId}-{$label}";
                    if (in_array($edgeKey, $savedEdges)) continue;
                    
                    $flow->edges()->create([
                        'source_node_id' => $sourceId,
                        'target_node_id' => $targetId,
                        'label' => $label
                    ]);
                    $savedEdges[] = $edgeKey;
                }
            }
            
            $flow->load(['nodes', 'edges']);
            return response()->json(['success' => true, 'flow' => $flow]);
        });
    }
    
    public function destroy($id) {
        $flow = auth()->user()->flows()->findOrFail($id);
        $flow->delete();
        return redirect()->route('workflows.index');
    }

    public function toggleActive(Request $request, $id) {
        $flow = auth()->user()->flows()->findOrFail($id);
        $flow->update(['active' => !$flow->active]);
        
        $message = $flow->active ? 'Workflow activated' : 'Workflow deactivated';
        
        if ($request->header('X-Inertia')) {
            return redirect()->back()->with('success', $message);
        }

        return response()->json([
            'success' => true, 
            'active' => (bool)$flow->active,
            'message' => $message
        ]);
    }
}
