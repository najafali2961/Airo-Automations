<?php

namespace App\Services;

use App\Models\Flow;
use App\Models\Node;
use App\Models\Execution;
use App\Flow\Engine\NodeExecutor;
use App\Flow\Engine\ConditionEvaluator;
use Illuminate\Support\Facades\Log;

class FlowEngine
{
    protected $nodeExecutor;

    public function __construct()
    {
        // Simple manual dependency injection for now, or use Laravel DI
        $this->nodeExecutor = new NodeExecutor(new ConditionEvaluator());
    }

    /**
     * Entry point for running a workflow.
     */
    public function run(Flow $flow, array $payload, string $topic, string $externalEventId)
    {
        // 1. Create Execution Record
        $execution = Execution::create([
            'flow_id' => $flow->id,
            'event' => $topic,
            'external_event_id' => $externalEventId,
            'payload' => $payload,
            'status' => 'running',
            'nodes_executed' => 0,
            'actions_completed' => 0
        ]);

        $this->log($execution, null, 'info', "Starting workflow execution for event: {$topic}", ['external_event_id' => $externalEventId]);

        try {
            // 2. Find Trigger Node
            // We search for a trigger node that matches the topic.
            // If topic is 'PRODUCTS_CREATE', it should match.
            $triggerNode = $flow->nodes()
                ->where('type', 'trigger')
                ->where(function ($query) use ($topic) {
                    $query->whereJsonContains('settings->topic', $topic)
                          ->orWhere('settings->event', $topic); // Support both formats
                })
                ->first();

            // Fallback to first trigger if no specific match (for generic triggers)
            if (!$triggerNode) {
                 $triggerNode = $flow->nodes()->where('type', 'trigger')->first();
            }

            if (!$triggerNode) {
                throw new \Exception("No trigger node found in workflow that matches topic: {$topic}");
            }

            // 3. Delegate execution to NodeExecutor
            $this->nodeExecutor->clearVisited();
            $this->nodeExecutor->run($triggerNode, $payload, $execution);

            // 4. Finalize Execution
            $execution->update(['status' => 'success']);
            $this->log($execution, null, 'info', "Workflow execution completed successfully.");

        } catch (\Throwable $e) {
            Log::error("Flow Execution Failed: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            $execution->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            $this->log($execution, null, 'error', "Execution failed: " . $e->getMessage());
        }
    }

    protected function log(Execution $execution, ?int $nodeId, string $level, string $message, ?array $data = null)
    {
        try {
            $execution->logs()->create([
                'node_id' => $nodeId,
                'level' => $level,
                'message' => $message,
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to create ExecutionLog: " . $e->getMessage());
        }
    }
}
