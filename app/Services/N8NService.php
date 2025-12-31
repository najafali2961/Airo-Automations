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
        $this->baseUrl = config('services.n8n.url', env('N8N_URL', 'https://primary-production-d49a.up.railway.app/api/v1'));
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
}
