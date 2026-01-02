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
             'flows' => auth()->user()->flows()->orderByDesc('updated_at')->get()
        ]);
    }

    public function editor($id = null) {
         $flow = $id ? auth()->user()->flows()->with(['nodes', 'edges'])->find($id) : null;
         
         if ($id && !$flow) abort(404);

         // Load Definitions
         $webhooks = config('shopify-app.webhooks');
         $shopifyTriggers = collect($webhooks)->map(function($webhook) {
             return [
                 'type' => 'trigger',
                 'n8nType' => 'shopifyTrigger', // Mark as Shopify specific
                 'label' => ucwords(strtolower(str_replace('_', ' ', $webhook['topic']))),
                 'description' => "Triggers when " . strtolower(str_replace('_', ' ', $webhook['topic'])),
                 'settings' => ['topic' => $webhook['topic']],
                 'group' => 'Shopify'
             ];
         })->values();

         $shopifyActions = [
             [
                 'type' => 'action',
                 'n8nType' => 'shopifyAction',
                 'label' => 'Create Product',
                 'settings' => ['resource' => 'Product', 'operation' => 'create'],
                 'group' => 'Shopify'
             ],
             [
                 'type' => 'action',
                 'n8nType' => 'shopifyAction',
                 'label' => 'Update Product',
                 'settings' => ['resource' => 'Product', 'operation' => 'update'],
                 'group' => 'Shopify'
             ],
             [
                 'type' => 'action',
                 'n8nType' => 'shopifyAction',
                 'label' => 'Add Tags to Customer',
                 'settings' => ['resource' => 'Customer', 'operation' => 'add_tags'],
                 'group' => 'Shopify'
             ]
         ];
         
         $definitions = [
             'apps' => [
                 [
                     'name' => 'Shopify',
                     'icon' => 'shopify', 
                     'triggers' => $shopifyTriggers,
                     'actions' => $shopifyActions
                 ]
             ]
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
                // Deleting nodes cascades to edges in DB if set up correctly, 
                // but explicit delete is safer for Eloquent events if we use them later.
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
            foreach ($validated['edges'] as $edgeData) {
                if (isset($nodeMap[$edgeData['source']]) && isset($nodeMap[$edgeData['target']])) {
                    $flow->edges()->create([
                        'source_node_id' => $nodeMap[$edgeData['source']],
                        'target_node_id' => $nodeMap[$edgeData['target']],
                        'label' => $edgeData['label'] ?? 'then' // Default edge type
                    ]);
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
}
