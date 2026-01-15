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

         // Fetch Active Connectors with their Active Triggers and Actions from DB
         $activeConnectors = \App\Models\Connector::query()
             ->where('is_active', true)
             ->with([
                 'triggers' => fn($q) => $q->where('is_active', true),
                 'actions' => fn($q) => $q->where('is_active', true)
             ])
             ->get();

         \Log::info("FlowController DB Debug: Found " . $activeConnectors->count() . " active connectors.");
         
         $formattedApps = [];

         foreach ($activeConnectors as $connector) {
             \Log::info("FlowController DB Debug: Connector {$connector->slug} has " . $connector->triggers->count() . " triggers and " . $connector->actions->count() . " actions.");
             
             // Map Triggers
             $triggers = $connector->triggers->map(function($t) use ($connector) {
                 \Log::info("FlowController Mapped: {$t->key} Topic: " . ($t->topic ?? 'NULL'));
                 return [
                     'type' => 'trigger',
                     'id' => $t->key,
                     'key' => $t->key,
                     'label' => $t->label,
                     'description' => $t->description,
                     'topic' => $t->topic,
                     'category' => $connector->slug,
                     'group' => $t->category ?? 'general',
                     'icon' => $t->icon,
                     'app' => $connector->slug,
                     'variables' => $t->variables ?? []
                 ];
             })->toArray();

             // Map Actions
             $actions = $connector->actions->map(function($a) use ($connector) {
                 return [
                     'type' => 'action',
                     'id' => $a->key,
                     'key' => $a->key,
                     'label' => $a->label,
                     'description' => $a->description,
                     'settings' => [
                         'action' => $a->key,
                     ],
                     'category' => $connector->slug,
                     'group' => $a->category ?? 'general',
                     'icon' => $a->icon,
                     'app' => $connector->slug,
                     'fields' => $a->fields ?? []
                 ];
             })->toArray();

             // Add to formatted list if valid
             if (!empty($triggers) || !empty($actions) || $connector->slug === 'shopify') {
                 $formattedApps[] = [
                     'name' => $connector->name,
                     'icon' => $connector->slug, 
                     'triggers' => $triggers,
                     'actions' => $actions
                 ];
             }
         }
         
         $definitions = [
             'apps' => $formattedApps
         ];

         // Fetch Connector Status for Validation
         $user = auth()->user();
         $activeSlugs = $user->activeConnectors()->pluck('connector_slug')->toArray();
         
         $connectors = [
             'google' => in_array('google', $activeSlugs),
             'slack' => in_array('slack', $activeSlugs),
             'smtp' => in_array('smtp', $activeSlugs),
             'klaviyo' => in_array('klaviyo', $activeSlugs),
         ];

         return Inertia::render('Workflows/Editor', [
             'flow' => $flow,
             'definitions' => $definitions,
             'connectors' => $connectors
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
                        'label' => $label,
                        'source_handle' => $edgeData['sourceHandle'] ?? null
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
