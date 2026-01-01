<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;

class N8NService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $url = config('services.n8n.url', env('N8N_URL'));
        if (!$url) {
            $baseUrl = env('N8N_BASE_URL', 'https://primary-production-d49a.up.railway.app');
            $url = rtrim($baseUrl, '/') . '/api/v1';
        }
        
        $this->baseUrl = $url;
        $this->apiKey = config('services.n8n.api_key', env('N8N_API_KEY'));
    }

    protected function client(): PendingRequest
    {
        return Http::withHeaders([
            'X-N8N-API-KEY' => $this->apiKey,
        ])->baseUrl($this->baseUrl);
    }

    /**
     * List all workflows.
     * Optionally filter by tags or other parameters supported by N8N.
     */
    public function listWorkflows(array $filters = [])
    {
        return $this->client()->get('/workflows', $filters)->json();
    }

    /**
     * Get a single workflow by ID.
     */
    public function getWorkflow(string $id)
    {
        return $this->client()->get("/workflows/{$id}")->json();
    }

    /**
     * Activate or Deactivate a workflow.
     */
    public function activateWorkflow(string $id, bool $active = true)
    {
        return $this->client()->post("/workflows/{$id}/" . ($active ? 'activate' : 'deactivate'))->json();
    }

    /**
     * Get executions.
     * Can filter by workflowId, etc.
     */
    public function getExecutions(array $filters = [])
    {
        return $this->client()->get('/executions', $filters)->json();
    }

    /**
     * Get a single execution detail.
     */
    public function getExecution(string $id)
    {
        return $this->client()->get("/executions/{$id}")->json();
    }

    /**
     * Get all available node types from N8N.
     */
    /**
     * Get all available node types from N8N.
     */
    public function getNodeTypes()
    {
        // Try the internal REST API which returns all node definitions
        // This is the endpoint the n8n frontend uses
        $rootUrl = str_replace('/api/v1', '', $this->baseUrl);
        $rootUrl = rtrim($rootUrl, '/');
        
        // Try /rest/node-types (older versions) or /rest/nodes (newer)
        $endpoints = [
             '/rest/node-types',
             '/rest/nodes',
             '/api/v1/node-types' // Just in case
        ];

        foreach ($endpoints as $endpoint) {
             $url = $rootUrl . $endpoint;
             \Illuminate\Support\Facades\Log::info("N8NService: Attempting to fetch node types from {$url}");
             
             try {
                // Internal API often uses the same Auth or cookie, but API Key often works for /rest
                $response = Http::withHeaders([
                    'X-N8N-API-KEY' => $this->apiKey,
                ])->get($url);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    // The structure might be { data: [...] } or just [...]
                    $nodes = $data['data'] ?? $data;
                    
                    if (is_array($nodes) && count($nodes) > 0) {
                         \Illuminate\Support\Facades\Log::info("N8NService: Successfully fetched " . count($nodes) . " node types from {$endpoint}");
                         return $nodes;
                    }
                }
             } catch (\Exception $e) {
                 continue;
             }
        }
        
        // Fallback: Harvest from existing workflows (Public API safe)
        return $this->harvestNodesFromWorkflows();
    }

    /**
     * Harvest unique node types from existing workflows and merge with standard library.
     */
    private function harvestNodesFromWorkflows()
    {
        \Illuminate\Support\Facades\Log::info("N8NService: Gathering node types (Standard + Harvested)...");
        
        $knownTypes = [];
        $nodes = [];

        // 1. Load Standard Library first
        $standardNodes = \App\Services\N8N\StandardNodes::get();
        foreach ($standardNodes as $node) {
            $knownTypes[$node['name']] = true;
            $nodes[] = $node;
        }

        try {
            // 2. Harvest from API (to find custom or installed community nodes)
            $response = $this->client()->get('/workflows');
            
            if ($response->successful()) {
                $workflows = $response->json('data') ?? [];
                $harvestedCount = 0;

                foreach ($workflows as $workflow) {
                    $wfNodes = $workflow['nodes'] ?? [];
                    foreach ($wfNodes as $node) {
                        $type = $node['type'] ?? null;
                        if ($type && !isset($knownTypes[$type])) {
                            $knownTypes[$type] = true;
                            $nodes[] = [
                                'name' => $type,
                                'displayName' => $node['typeVersion'] > 1 ? $node['name'] : $this->formatNodeName($type),
                                'group' => ['harvested'],
                                'response_type' => 'harvested'
                            ];
                            $harvestedCount++;
                        }
                    }
                }
                 \Illuminate\Support\Facades\Log::info("N8NService: Added {$harvestedCount} custom/harvested nodes from existing workflows.");
            } else {
                 \Illuminate\Support\Facades\Log::warning("N8NService: Could not harvest workflows (Status {$response->status()}). Using standard library only.");
            }

            $count = count($nodes);
            \Illuminate\Support\Facades\Log::info("N8NService: Returning total {$count} node types.");
            
            // Sort alphabetically by displayName for better UI
            usort($nodes, function ($a, $b) {
                return strcmp($a['displayName'], $b['displayName']);
            });

            return $nodes;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("N8NService: Exception during node harvesting", ['error' => $e->getMessage()]);
            // Still return standard nodes even if harvest fails
            return $nodes; 
        }
    }

    private function formatNodeName($type)
    {
        // Simple formatter: n8n-nodes-base.httpRequest -> Http Request
        $parts = explode('.', $type);
        $name = end($parts);
        return ucwords(preg_replace('/(?<!^)[A-Z]/', ' $0', $name));
    }

    public function getBaseUrl() 
    {
        return $this->baseUrl;
    }
}
