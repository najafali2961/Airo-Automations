<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Template;
use App\Models\Flow;
use App\Models\Node;
use App\Models\Edge;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TemplateController extends Controller
{
    public function index()
    {
        return Inertia::render('Templates/Index', [
            'templates' => Template::all()
        ]);
    }

    public function activate(Request $request, Template $template)
    {
        $shop = Auth::user();
        
        $structure = $template->workflow_data ?? [];
        $nodes = $structure['nodes'] ?? [];
        $edges = $structure['edges'] ?? [];
        
        // Default content if empty
        if (empty($nodes)) {
            $nodes = [
                [
                    'id' => 'trigger_1',
                    'type' => 'trigger',
                    'position_x' => 250,
                    'position_y' => 50,
                    'label' => 'Manual Trigger',
                    'settings' => ['triggerType' => 'manual'] 
                ]
            ];
            $edges = [];
        }

        // Create Flow
        $flow = new Flow();
        $flow->shop_id = $shop->id;
        $flow->name = $template->name; 
        $flow->description = $template->description;
        $flow->active = false;
        $flow->save();

        $nodeIdMap = [];

        // Create Nodes
        foreach ($nodes as $nodeData) {
            $node = new Node();
            $node->flow_id = $flow->id;
            $node->type = $nodeData['type'];
            
            // Handle both flat and nested (React Flow) structures
            $node->label = $nodeData['label'] ?? $nodeData['data']['label'] ?? null;
            $node->settings = $nodeData['settings'] ?? $nodeData['data']['settings'] ?? [];
            
            // Handle position formats (flat or nested)
            $x = $nodeData['position_x'] ?? ($nodeData['position']['x'] ?? 0);
            $y = $nodeData['position_y'] ?? ($nodeData['position']['y'] ?? 0);
            
            $node->position_x = $x;
            $node->position_y = $y;
            $node->save();
            
            if (isset($nodeData['id'])) {
                $nodeIdMap[$nodeData['id']] = $node->id;
            }
        }

        // Create Edges
        foreach ($edges as $edgeData) {
            $sourceId = $edgeData['source'] ?? null;
            $targetId = $edgeData['target'] ?? null;

            if ($sourceId && $targetId && isset($nodeIdMap[$sourceId]) && isset($nodeIdMap[$targetId])) {
                $edge = new Edge();
                $edge->flow_id = $flow->id;
                $edge->source_node_id = $nodeIdMap[$sourceId];
                $edge->target_node_id = $nodeIdMap[$targetId];
                $edge->label = $edgeData['label'] ?? null;
                $edge->source_handle = $edgeData['sourceHandle'] ?? $edgeData['source_handle'] ?? null;
                $edge->save();
            }
        }

        return redirect()->route('editor', ['id' => $flow->id]);
    }
}
