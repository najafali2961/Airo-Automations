<?php

namespace App\Flow\Engine;

use App\Models\Node;
use App\Models\Execution;
use Illuminate\Support\Facades\Log;

class NodeExecutor
{
    protected $visited = [];
    protected $maxDepth = 100;
    protected $conditionEvaluator;

    public function __construct(ConditionEvaluator $conditionEvaluator)
    {
        $this->conditionEvaluator = $conditionEvaluator;
    }

    public function clearVisited(): void
    {
        $this->visited = [];
    }

    public function run(Node $node, array $data, Execution $execution, array $stack = []): void
    {
        if (in_array($node->id, $stack)) {
            $this->log($execution, $node->id, 'error', "Infinite loop detected at node #{$node->id}");
            throw new \Exception("Infinite loop detected at node #{$node->id}");
        }

        if (count($this->visited) >= $this->maxDepth) {
            $this->log($execution, $node->id, 'error', "Max execution depth ({$this->maxDepth}) reached.");
            throw new \Exception("Max execution depth reached.");
        }

        $stack[] = $node->id;
        $this->visited[] = $node->id;
        $execution->increment('nodes_executed');

        $this->log($execution, $node->id, 'info', "Executing node: {$node->label} ({$node->type})");

        try {
            $nextPath = 'then';

            switch ($node->type) {
                case 'trigger':
                    // Triggers always move to 'then'
                    break;

                case 'condition':
                    $settings = $this->getSettings($node);
                    $result = $this->conditionEvaluator->evaluate($settings, $data);
                    $nextPath = $result ? 'true' : 'false';
                    $this->log($execution, $node->id, 'info', "Condition evaluated to: " . ($result ? 'TRUE' : 'FALSE'));
                    break;

                case 'action':
                    $this->executeAction($node, $data, $execution);
                    $execution->increment('actions_completed');
                    break;
            }

            $nextNodes = $node->nextNodes($nextPath);
            
            // If path-specific nodes not found:
            if ($nextNodes->isEmpty() && $nextPath !== 'then') {
                 // For conditions: 'true' can fall back to 'then' (legacy/generic edge)
                 // But 'false' should NOT fall back to 'then'
                 if ($node->type === 'condition' && $nextPath === 'true') {
                     $nextNodes = $node->nextNodes('then');
                 } else if ($node->type !== 'condition') {
                     $nextNodes = $node->nextNodes('then');
                 }
            }

            if ($nextNodes->isNotEmpty()) {
                $this->log($execution, $node->id, 'info', "Transitioning to " . count($nextNodes) . " node(s) on path: '{$nextPath}'");
            } else {
                $this->log($execution, $node->id, 'info', "No next nodes found for path: '{$nextPath}'. Stopping branch.");
            }

            foreach ($nextNodes as $nextNode) {
                $this->run($nextNode, $data, $execution, $stack);
            }

        } catch (\Throwable $e) {
            $this->log($execution, $node->id, 'error', "Node execution failed: " . $e->getMessage());
            
            $errorNodes = $node->nextNodes('error');
            if ($errorNodes->isNotEmpty()) {
                foreach ($errorNodes as $errorNode) {
                    $this->run($errorNode, $data, $execution);
                }
            } else {
                throw $e;
            }
        }
    }

    protected function executeAction(Node $node, array $data, Execution $execution): void
    {
        $actionKey = $node->settings['action'] ?? null;
        
        if (!$actionKey) {
            $this->log($execution, $node->id, 'warning', "Action node has no action configured.");
            return;
        }

        // Context-aware dynamic mapping (Legacy support / Conveniences)
        if ($actionKey === 'add_tag') {
            $topic = strtoupper($execution->event);
            if (str_contains($topic, 'PRODUCT')) $actionKey = 'add_product_tag';
            elseif (str_contains($topic, 'ORDER')) $actionKey = 'add_order_tag';
            elseif (str_contains($topic, 'CUSTOMER')) $actionKey = 'add_customer_tag';
        }

        $action = ActionRegistry::getAction($actionKey);

        if (!$action) {
            $this->log($execution, $node->id, 'error', "Action implementation not found: {$actionKey}");
            return;
        }

        $action->handle($node, $data, $execution);
    }

    protected function getSettings(Node $node): array
    {
        $settings = $node->settings ?? [];
        if (!empty($settings['form']) && is_array($settings['form'])) {
            $settings = array_merge($settings, $settings['form']);
        }
        return $settings;
    }

    protected function log(Execution $execution, ?int $nodeId, string $level, string $message): void
    {
        $execution->logs()->create([
            'node_id' => $nodeId,
            'level' => $level,
            'message' => $message
        ]);
    }
}
