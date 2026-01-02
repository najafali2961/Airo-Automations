<?php

namespace App\Services;

use App\Models\Flow;
use App\Models\Node;
use App\Models\Execution;
use Illuminate\Support\Facades\Log;

class FlowExecutor
{
    protected $maxDepth = 50;

    public function executeFlow(Flow $flow, array $data, Execution $execution)
    {
        $startTime = microtime(true);
        
        $trigger = $flow->nodes()->where('type', 'trigger')->first();
        
        if (!$trigger) {
            throw new \Exception("No trigger node found.");
        }
        
        $this->runNode($trigger, $data, $execution, [], 0);
        
        $duration = (microtime(true) - $startTime) * 1000;
        
        $execution->update([
            'status' => 'success',
            'duration_ms' => round($duration)
        ]);
    }

    protected function runNode(Node $node, array $data, Execution $execution, array $visited, int $depth)
    {
        if ($depth > $this->maxDepth) throw new \Exception("Max depth exceeded.");
        if (in_array($node->id, $visited)) throw new \Exception("Cycle detected.");
        
        $visited[] = $node->id;
        $execution->increment('nodes_executed');
        
        try {
            switch ($node->type) {
                case 'trigger':
                    // Just proceed
                    $this->next($node, 'then', $data, $execution, $visited, $depth);
                    break;
                    
                case 'condition':
                    $result = $this->evaluateCondition($node, $data);
                    $label = $result ? 'true' : 'false';
                    $this->next($node, $label, $data, $execution, $visited, $depth);
                    break;
                    
                case 'action':
                    $this->performAction($node, $data);
                    $execution->increment('actions_completed');
                    $this->next($node, 'then', $data, $execution, $visited, $depth);
                    break;
            }
        } catch (\Exception $e) {
            // Check for error edge
            $errorNodes = $node->nextNodes('error');
            if ($errorNodes->isNotEmpty()) {
                foreach ($errorNodes as $next) {
                    $this->runNode($next, $data, $execution, $visited, $depth + 1);
                }
            } else {
                throw $e;
            }
        }
    }
    
    protected function next($node, $label, $data, $execution, $visited, $depth) {
        $nextNodes = $node->nextNodes($label);
        foreach ($nextNodes as $next) {
            $this->runNode($next, $data, $execution, $visited, $depth + 1);
        }
    }
    
    protected function evaluateCondition($node, $data) {
        $settings = $node->settings;
        $logic = $settings['logic'] ?? 'AND';
        $rules = $settings['rules'] ?? [];
        
        $results = [];
        foreach ($rules as $rule) {
            $value = data_get($data, $rule['field']); // simplified field access
            $operator = $rule['operator'];
            $target = $rule['value'];
            
            $match = false;
            switch ($operator) {
                case '=': $match = $value == $target; break;
                case '!=': $match = $value != $target; break;
                case '>': $match = $value > $target; break;
                case '<': $match = $value < $target; break;
                case '>=': $match = $value >= $target; break;
                case '<=': $match = $value <= $target; break;
                case 'contains': $match = str_contains((string)$value, (string)$target); break;
            }
            $results[] = $match;
        }
        
        if (empty($results)) return true; // No rules = true?
        
        if ($logic === 'AND') {
            return !in_array(false, $results, true);
        } else {
            return in_array(true, $results, true);
        }
    }
    
    protected function performAction($node, $data) {
        $action = $node->settings['action'] ?? null;
        Log::info("Performing action: $action", ['data' => $data]);
        // Implement specific actions here
        // e.g., using Shopify API
    }
}
