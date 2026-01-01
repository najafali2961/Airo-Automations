<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;

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

    public function client(): PendingRequest
    {
        return Http::withHeaders([
            'X-N8N-API-KEY' => $this->apiKey,
        ])->baseUrl($this->baseUrl);
    }

    // --- WORKFLOWS ---

    public function listWorkflows(array $filters = [])
    {
        return $this->client()->get('/workflows', $filters)->json();
    }

    public function createWorkflow(array $data)
    {
        return $this->client()->post('/workflows', $data)->json();
    }

    public function getWorkflow(string $id, array $params = [])
    {
        return $this->client()->get("/workflows/{$id}", $params)->json();
    }

    public function updateWorkflow(string $id, array $data)
    {
        return $this->client()->put("/workflows/{$id}", $data)->json();
    }

    public function deleteWorkflow(string $id)
    {
        return $this->client()->delete("/workflows/{$id}")->json();
    }

    public function activateWorkflow(string $id)
    {
        return $this->client()->post("/workflows/{$id}/activate")->json();
    }

    public function deactivateWorkflow(string $id)
    {
        return $this->client()->post("/workflows/{$id}/deactivate")->json();
    }

    // --- EXECUTIONS ---

    public function getExecutions(array $filters = [])
    {
        return $this->client()->get('/executions', $filters)->json();
    }

    public function getExecution(string $id, array $params = [])
    {
        return $this->client()->get("/executions/{$id}", $params)->json();
    }

    public function deleteExecution(string $id)
    {
        return $this->client()->delete("/executions/{$id}")->json();
    }

    public function retryExecution(string $id, array $data = [])
    {
        return $this->client()->post("/executions/{$id}/retry", $data)->json();
    }

    // --- CREDENTIALS ---

    public function listCredentials(array $filters = [])
    {
        return $this->client()->get('/credentials', $filters)->json('data') ?? [];
    }

    public function createCredential(array $data)
    {
        return $this->client()->post('/credentials', $data)->json();
    }

    public function deleteCredential(string $id)
    {
        return $this->client()->delete("/credentials/{$id}")->json();
    }

    public function getCredentialSchema(string $typeName)
    {
        return $this->client()->get("/credentials/schema/{$typeName}")->json();
    }

    // --- TAGS ---

    public function listTags(array $filters = [])
    {
        return $this->client()->get('/tags', $filters)->json();
    }

    public function createTag(array $data)
    {
        return $this->client()->post('/tags', $data)->json();
    }

    public function getTag(string $id)
    {
        return $this->client()->get("/tags/{$id}")->json();
    }

    public function updateTag(string $id, array $data)
    {
        return $this->client()->put("/tags/{$id}", $data)->json();
    }

    public function deleteTag(string $id)
    {
        return $this->client()->delete("/tags/{$id}")->json();
    }

    // --- VARIABLES ---

    public function listVariables(array $filters = [])
    {
        return $this->client()->get('/variables', $filters)->json();
    }

    public function createVariable(array $data)
    {
        return $this->client()->post('/variables', $data)->json();
    }

    public function updateVariable(string $id, array $data)
    {
        return $this->client()->put("/variables/{$id}", $data)->json();
    }

    public function deleteVariable(string $id)
    {
        return $this->client()->delete("/variables/{$id}")->json();
    }

    // --- NODE TYPES DISCOVERY ---

    public function getNodeTypes()
    {
        $rootUrl = str_replace('/api/v1', '', $this->baseUrl);
        $rootUrl = rtrim($rootUrl, '/');
        
        Log::info("N8NService: Starting comprehensive node discovery.");

        // 1. Get Official Catalog from GitHub (Full coverage)
        $catalog = $this->getCatalogFromGitHub();
        $nodesMap = [];
        foreach ($catalog as $node) {
            $nodesMap[$node['name']] = $node;
        }

        // 2. Enrich with live nodes from n8n internal API if accessible
        $endpoints = [
             '/rest/node-types',
             '/rest/nodes',
        ];

        foreach ($endpoints as $endpoint) {
             try {
                $response = Http::withHeaders(['X-N8N-API-KEY' => $this->apiKey])->get($rootUrl . $endpoint);
                
                if ($response->successful()) {
                    $liveNodes = $response->json('data') ?? $response->json();
                    if (is_array($liveNodes) && !empty($liveNodes)) {
                         Log::info("N8NService: Merging " . count($liveNodes) . " live nodes.");
                         foreach ($liveNodes as $node) {
                             $name = $node['name'] ?? null;
                             if ($name) {
                                 // Prioritize live data but keep catalog info if live is sparse
                                 $nodesMap[$name] = array_merge($nodesMap[$name] ?? [], $node);
                             }
                         }
                         // If we got live nodes, we return the merge
                         return array_values($nodesMap);
                    }
                }
             } catch (\Exception $e) {
                 continue;
             }
        }
        
        // 3. Last Fallback: Harvest from existing workflows for custom/community nodes
        $harvested = $this->harvestNodesFromWorkflows();
        foreach ($harvested as $node) {
            $name = $node['name'] ?? null;
            if ($name && !isset($nodesMap[$name])) {
                $nodesMap[$name] = $node;
            }
        }

        return array_values($nodesMap);
    }

    protected function getCatalogFromGitHub()
    {
        return cache()->remember('n8n_node_catalog', 86400, function () {
            try {
                Log::info("N8NService: Syncing node catalog from GitHub...");
                $url = "https://api.github.com/repos/n8n-io/n8n/contents/packages/nodes-base/nodes";
                $response = Http::withHeaders([
                    'User-Agent' => 'Laravel-N8N-App'
                ])->get($url);

                if ($response->successful()) {
                    $dirs = $response->json();
                    $catalog = [];
                    foreach ($dirs as $dir) {
                        if ($dir['type'] === 'dir') {
                            $rawName = $dir['name'];
                            $internalName = 'n8n-nodes-base.' . lcfirst($rawName);
                            
                            $catalog[] = [
                                'name' => $internalName,
                                'displayName' => $this->formatNodeName($internalName),
                                'group' => str_contains($rawName, 'Trigger') ? ['trigger', 'catalog'] : ['catalog'],
                                'description' => "Official n8n node: {$rawName}"
                            ];
                        }
                    }
                    Log::info("N8NService: Catalog sync complete. Found " . count($catalog) . " nodes.");
                    return $catalog;
                }
            } catch (\Exception $e) {
                Log::error("N8NService: GitHub sync failed", ['error' => $e->getMessage()]);
            }
            return [];
        });
    }

    protected function harvestNodesFromWorkflows()
    {
        $nodes = [];
        $knownTypes = [];

        try {
            $response = $this->listWorkflows();
            $workflows = $response['data'] ?? [];
            Log::info("N8NService: Harvesting nodes from " . count($workflows) . " workflows.");

            foreach ($workflows as $workflow) {
                $wfNodes = $workflow['nodes'] ?? [];
                foreach ($wfNodes as $node) {
                    $type = $node['type'] ?? null;
                    if ($type && !isset($knownTypes[$type])) {
                        $knownTypes[$type] = true;
                        $nodes[] = [
                            'name' => $type,
                            'displayName' => $this->formatNodeName($type),
                            'group' => ['harvested']
                        ];
                        Log::debug("N8NService: Harvested new node type: {$type}");
                    }
                }
            }
            Log::info("N8NService: Total unique nodes harvested: " . count($nodes));
        } catch (\Exception $e) {
            Log::error("N8NService: Failed to harvest nodes", ['error' => $e->getMessage()]);
        }

        return $nodes;
    }

    protected function formatNodeName(string $type)
    {
        $parts = explode('.', $type);
        $name = end($parts);
        return ucwords(preg_replace('/(?<!^)[A-Z]/', ' $0', $name));
    }

    public function getBaseUrl() 
    {
        return $this->baseUrl;
    }
}
