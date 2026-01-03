<?php

namespace App\Services;

use App\Models\Flow;
use App\Models\Execution;
use App\Flow\Engine\NodeExecutor;
use App\Flow\Engine\ConditionEvaluator;
use Illuminate\Support\Facades\Log;

class FlowExecutor
{
    protected $nodeExecutor;

    public function __construct()
    {
        $this->nodeExecutor = new NodeExecutor(new ConditionEvaluator());
    }

    /**
     * Execute a flow from a pre-created execution record.
     */
    public function executeFlow(Flow $flow, array $data, Execution $execution)
    {
        $startTime = microtime(true);
        
        try {
            // Find trigger node (by topic/event)
            $topic = $execution->event;
            $triggerNode = $flow->nodes()
                ->where('type', 'trigger')
                ->where(function ($query) use ($topic) {
                    $query->whereJsonContains('settings->topic', $topic)
                          ->orWhere('settings->event', $topic);
                })
                ->first();

            if (!$triggerNode) {
                // Secondary check for legacy formats or generic triggers
                $triggerNode = $flow->nodes()->where('type', 'trigger')->first();
            }

            if (!$triggerNode) {
                throw new \Exception("No trigger node found for execution.");
            }

            $this->nodeExecutor->run($triggerNode, $data, $execution);

            $duration = (microtime(true) - $startTime) * 1000;
            $execution->update([
                'status' => 'success',
                'duration_ms' => round($duration)
            ]);

        } catch (\Throwable $e) {
            $duration = (microtime(true) - $startTime) * 1000;
            $execution->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'duration_ms' => round($duration)
            ]);
            throw $e;
        }
    }
}
