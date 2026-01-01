<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Workflow;
use App\Services\N8NService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class WorkflowController extends Controller
{
    protected $n8nService;

    public function __construct(N8NService $n8nService)
    {
        $this->n8nService = $n8nService;
    }

    /**
     * List all workflows for the current shop
     */
    public function index()
    {
        $shop = Auth::user();
        $workflows = $shop->workflows()->orderBy('updated_at', 'desc')->get();

        // Fetch N8N Workflows for listing
        $n8nWorkflows = [];
        try {
            $response = $this->n8nService->listWorkflows();
            $n8nWorkflows = $response['data'] ?? [];
        } catch (\Exception $e) {
            // Log silent error
        }

        return Inertia::render('Workflows/Index', [
            'workflows' => $workflows,
            'n8nWorkflows' => $n8nWorkflows
        ]);
    }

    /**
     * Show the editor for a specific workflow (or new)
     */
    public function editor(Request $request, $id = null)
    {
        $shop = Auth::user();
        $workflow = null;

        if ($id && $id !== 'new') {
            $workflow = $shop->workflows()->findOrFail($id);
        }

        return Inertia::render('WorkflowEditor', [
            'shop' => $shop,
            'workflow' => $workflow
        ]);
    }

    /**
     * Save (Create or Update) a workflow
     */
    public function save(Request $request, \App\Services\WorkflowTransformer $transformer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'workflow_ui' => 'required|array', // { nodes: [], edges: [] }
            'id' => 'nullable|exists:workflows,id'
        ]);

        $shop = Auth::user();
        $uiData = $request->input('workflow_ui');
        $uiData['name'] = $request->input('name');
        
        // Transform logic moved to Backend
        $n8nData = $transformer->toN8n($uiData);
        $name = $request->input('name');
        
        // 1. Sync with N8N
        $n8nId = null;
        
        // If updating an existing local workflow, try to update on N8N
        if ($request->id) {
            $existing = $shop->workflows()->find($request->id);
            if ($existing && $existing->n8n_id) {
               // Update N8N
               $n8nData['id'] = $existing->n8n_id; 
               $n8nData['name'] = $name;
               
               $response = $this->n8nService->client()->put("/workflows/{$existing->n8n_id}", $n8nData);
               if ($response->successful()) {
                   $n8nId = $existing->n8n_id;
               } else {
                   Log::error("N8N Update Failed", ['error' => $response->body()]);
               }
            }
        }
        
        // If new or N8N update failed/didn't exist, Create new on N8N
        if (!$n8nId) {
            $n8nData['name'] = $name;
            $response = $this->n8nService->client()->post('/workflows', $n8nData);
            if ($response->successful()) {
                $n8nId = $response->json('id');
            } else {
                return back()->withErrors(['n8n' => 'Failed to create workflow in N8N Engine: ' . $response->body()]);
            }
        }

        // 2. Save Locally
        $workflow = $shop->workflows()->updateOrCreate(
            ['id' => $request->id],
            [
                'name' => $name,
                'n8n_id' => $n8nId,
                'ui_data' => $uiData,
                'status' => true, // Active by default
            ]
        );

        return redirect()->route('editor', ['id' => $workflow->id])->with('success', 'Workflow saved successfully!');
    }

    /**
     * Delete a workflow
     */
    public function destroy($id)
    {
        $shop = Auth::user();
        $workflow = $shop->workflows()->findOrFail($id);

        // Delete from N8N
        if ($workflow->n8n_id) {
            $this->n8nService->client()->delete("/workflows/{$workflow->n8n_id}");
        }

        $workflow->delete();

        return redirect()->route('workflows.index')->with('success', 'Workflow deleted.');
    }
    /**
     * Get executions for a specific workflow
     */
    public function executions($id)
    {
        $shop = Auth::user();
        $workflow = $shop->workflows()->findOrFail($id);

        try {
            if (!$workflow->n8n_id) {
                return response()->json(['data' => []]);
            }

            // Fetch from N8N
            // N8N filter: workflowId
            $data = $this->n8nService->getExecutions([
                'workflowId' => $workflow->n8n_id,
                'limit' => 20
            ]);

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error("Failed to fetch executions", ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch executions'], 500);
        }
    }

    /**
     * Activate a workflow
     */
    public function activate($id)
    {
        $shop = Auth::user();
        $workflow = $shop->workflows()->findOrFail($id);

        if ($workflow->n8n_id) {
            $response = $this->n8nService->activateWorkflow($workflow->n8n_id, true);
            // Check response if needed, N8N usually returns the workflow object
        }

        $workflow->update(['status' => true]);

        return back()->with('success', 'Workflow activated.');
    }

    /**
     * Deactivate a workflow
     */
    public function deactivate($id)
    {
        $shop = Auth::user();
        $workflow = $shop->workflows()->findOrFail($id);

        if ($workflow->n8n_id) {
            $this->n8nService->activateWorkflow($workflow->n8n_id, false);
        }

        $workflow->update(['status' => false]);

        return back()->with('success', 'Workflow deactivated.');
    }

    /**
     * Get all N8N node types
     */
    public function nodeTypes()
    {
        \Illuminate\Support\Facades\Log::info("WorkflowController: Received request for node types.");
        try {
            $types = $this->n8nService->getNodeTypes();
            \Illuminate\Support\Facades\Log::info("WorkflowController: Returning " . count($types) . " types to frontend.");
            return response()->json($types);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to fetch node types", ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch node types'], 500);
        }
    }
    

    
    public function testConnection()
    {
        try {
            $types = $this->n8nService->getNodeTypes();
            $count = count($types ?? []);
            
            return response()->json([
                'status' => 'success',
                'message' => "Successfully connected to N8N.",
                'node_count' => $count,
                'first_node' => $count > 0 ? $types[0] : null,
                'n8n_url' => $this->n8nService->getBaseUrl()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to connect to N8N.',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
