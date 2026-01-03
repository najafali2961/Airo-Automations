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

    public function run(Node $node, array $data, Execution $execution): void
    {
        if (in_array($node->id, $this->visited)) {
            $this->log($execution, $node->id, 'error', "Cycle detected at node #{$node->id}");
            throw new \Exception("Cycle detected at node #{$node->id}");
        }

        if (count($this->visited) >= $this->maxDepth) {
            $this->log($execution, $node->id, 'error', "Max execution depth ({$this->maxDepth}) reached.");
            throw new \Exception("Max execution depth reached.");
        }

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
                    $result = $this->conditionEvaluator->evaluate($node->settings, $data);
                    $nextPath = $result ? 'true' : 'false';
                    $this->log($execution, $node->id, 'info', "Condition evaluated to: " . ($result ? 'TRUE' : 'FALSE'));
                    break;

                case 'action':
                    $this->executeAction($node, $data, $execution);
                    $execution->increment('actions_completed');
                    break;
            }

            $nextNodes = $node->nextNodes($nextPath);
            
            // If path-specific nodes not found, try generic 'then' for conditions/actions
            if ($nextNodes->isEmpty() && $nextPath !== 'then') {
                 $nextNodes = $node->nextNodes('then');
            }

            foreach ($nextNodes as $nextNode) {
                $this->run($nextNode, $data, $execution);
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
            $topic = $execution->event;
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

    protected function log(Execution $execution, ?int $nodeId, string $level, string $message): void
    {
        $execution->logs()->create([
            'node_id' => $nodeId,
            'level' => $level,
            'message' => $message
        ]);
    }
}
