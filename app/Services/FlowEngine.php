<?php

namespace App\Services;

use App\Models\Flow;
use App\Models\Node;
use App\Models\Execution;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class FlowEngine
{
    protected $execution;
    protected $visited = [];
    protected $maxDepth = 100;

    public function run(Flow $flow, array $payload, string $topic, string $externalEventId)
    {
        // idempotency check could go here or before calling run
        
        // Create Execution Record
        $this->execution = Execution::create([
            'flow_id' => $flow->id,
            'event' => $topic,
            'external_event_id' => $externalEventId,
            'payload' => $payload,
            'status' => 'running',
            'nodes_executed' => 0
        ]);

        try {
            // Find Trigger Node
            $triggerNode = $flow->nodes()
                ->where('type', 'trigger')
                ->whereJsonContains('settings->topic', $topic)
                ->first();

            if (!$triggerNode) {
                 // Try finding any generic trigger if exact topic match fails (fallback)
                 $triggerNode = $flow->nodes()->where('type', 'trigger')->first();
                 // Validate topic if needed, but for now assume caller filtered flows by topic
            }

            if ($triggerNode) {
                $this->runNode($triggerNode, $payload);
            }

            $this->execution->update(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error("Flow Execution Failed: " . $e->getMessage());
            $this->execution->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
        }
    }

    protected function runNode(Node $node, array $data)
    {
        if (in_array($node->id, $this->visited) || count($this->visited) > $this->maxDepth) {
            throw new \Exception("Cycle detected or max depth exceeded at Node {$node->id}");
        }
        $this->visited[] = $node->id;
        $this->execution->increment('nodes_executed');

        $nextNodes = collect();

        try {
            // Execute Node Logic
            switch ($node->type) {
                case 'trigger':
                    // Just pass through
                    $nextNodes = $node->nextNodes('then');
                    break;

                case 'condition':
                    $result = $this->evaluateCondition($node->settings, $data);
                    // Use 'true' or 'false' label edges
                    $label = $result ? 'true' : 'false';
                    $nextNodes = $node->nextNodes($label);
                    // Fallback to 'then' if no logic branches found? usually condition has true/false
                    if ($nextNodes->isEmpty()) {
                        // maybe 'then' used as default?
                        $nextNodes = $node->nextNodes('then');
                    }
                    break;

                case 'action':
                    $this->executeAction($node->settings, $data);
                    $this->execution->increment('actions_completed');
                    $nextNodes = $node->nextNodes('then');
                    break;
            }

            // Recursive Call
            foreach ($nextNodes as $nextNode) {
                $this->runNode($nextNode, $data);
            }

        } catch (\Exception $e) {
            // Handle error branches if any
            $errorNodes = $node->nextNodes('error');
            if ($errorNodes->isNotEmpty()) {
                foreach ($errorNodes as $errorNode) {
                    $this->runNode($errorNode, $data);
                }
            } else {
                throw $e; // Re-throw to fail execution
            }
        }
    }

    protected function evaluateCondition($settings, $data)
    {
        // Simple Rule Evaluation
        // Structure: { rules: [ { object, field, operator, value } ], logic: 'AND' }
        $rules = $settings['rules'] ?? [];
        $logic = $settings['logic'] ?? 'AND';
        
        if (empty($rules)) return true;

        $results = [];
        foreach ($rules as $rule) {
            $fieldValue = $this->getValueFromPayload($data, $rule['object'], $rule['field']);
            $results[] = $this->compare($fieldValue, $rule['operator'], $rule['value']);
        }

        if ($logic === 'AND') {
            return !in_array(false, $results);
        } else {
            return in_array(true, $results);
        }
    }

    protected function executeAction($settings, $data)
    {
        $action = $settings['action'] ?? null;
        $form = $settings['form'] ?? [];

        if (!$action) return;

        // Simplified Action Dispatcher
        switch ($action) {
            case 'log_output':
                Log::info("Workflow Log: " . ($form['message'] ?? ''));
                break;
            
            case 'http_request':
                $method = $form['method'] ?? 'GET';
                $url = $form['url'] ?? '';
                if ($url) {
                    Http::withHeaders(['Content-Type' => 'application/json'])
                        ->$method($url, json_decode($form['body'] ?? '{}', true));
                }
                break;
                
            case 'add_product_tag':
                $this->addProductTag($data, $form);
                break;
                
            case 'add_order_tag':
                $this->addOrderTag($data, $form);
                break;
        }
    }

    protected function getValueFromPayload($payload, $object, $field)
    {
        // Support specific objects mapping or just data traversal
        // Simplified: just check if key exists in top level or nested dot notation
        
        // If object is 'order' and payload is order, ignore object prefix?
        // Or if payload has { 'order': ... }?
        // Shopify webhooks usually send the resource as root. e.g. orders/create -> order data.
        
        return data_get($payload, $field);
    }

    protected function compare($value1, $operator, $value2)
    {
        switch ($operator) {
            case 'equals': return $value1 == $value2;
            case 'not_equals': return $value1 != $value2;
            case 'greater_than': return $value1 > $value2;
            case 'less_than': return $value1 < $value2;
            case 'contains': return is_string($value1) && str_contains($value1, $value2);
            default: return false;
        }
    }

    protected function addProductTag($data, $form)
    {
        try {
            $productId = $data['id'] ?? $data['admin_graphql_api_id'] ?? null;
            $tag = $form['tag'] ?? null;
            
            if (!$productId || !$tag) {
                Log::warning("Missing product ID or tag for add_product_tag action");
                return;
            }

            // Extract numeric ID if GID
            if (strpos($productId, 'gid://') === 0) {
                $productId = (int) basename($productId);
            }

            // Get shop from execution context
            $flow = $this->execution->flow;
            $userModel = config('auth.providers.users.model');
            $shop = $userModel::find($flow->shop_id);

            if (!$shop) {
                throw new \Exception("Shop not found for flow execution");
            }

            // Fetch current tags
            $response = $shop->api()->rest('GET', "/admin/api/2024-07/products/{$productId}.json");
            $product = $response['body']['product'] ?? null;
            
            if (!$product) {
                throw new \Exception("Product not found");
            }

            $currentTags = $product['tags'] ?? '';
            $tagsArray = array_filter(array_map('trim', explode(',', $currentTags)));
            
            // Add new tag if not exists
            if (!in_array($tag, $tagsArray)) {
                $tagsArray[] = $tag;
            }

            // Update product with new tags
            $shop->api()->rest('PUT', "/admin/api/2024-07/products/{$productId}.json", [
                'product' => [
                    'id' => $productId,
                    'tags' => implode(', ', $tagsArray)
                ]
            ]);

            Log::info("Added tag '{$tag}' to product {$productId}");
        } catch (\Exception $e) {
            Log::error("Failed to add product tag", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    protected function addOrderTag($data, $form)
    {
        try {
            $orderId = $data['id'] ?? $data['admin_graphql_api_id'] ?? null;
            $tag = $form['tag'] ?? null;
            
            if (!$orderId || !$tag) {
                Log::warning("Missing order ID or tag for add_order_tag action");
                return;
            }

            // Extract numeric ID if GID
            if (strpos($orderId, 'gid://') === 0) {
                $orderId = (int) basename($orderId);
            }

            // Get shop from execution context
            $flow = $this->execution->flow;
            $userModel = config('auth.providers.users.model');
            $shop = $userModel::find($flow->shop_id);

            if (!$shop) {
                throw new \Exception("Shop not found for flow execution");
            }

            // Fetch current tags
            $response = $shop->api()->rest('GET', "/admin/api/2024-07/orders/{$orderId}.json");
            $order = $response['body']['order'] ?? null;
            
            if (!$order) {
                throw new \Exception("Order not found");
            }

            $currentTags = $order['tags'] ?? '';
            $tagsArray = array_filter(array_map('trim', explode(',', $currentTags)));
            
            // Add new tag if not exists
            if (!in_array($tag, $tagsArray)) {
                $tagsArray[] = $tag;
            }

            // Update order with new tags
            $shop->api()->rest('PUT', "/admin/api/2024-07/orders/{$orderId}.json", [
                'order' => [
                    'id' => $orderId,
                    'tags' => implode(', ', $tagsArray)
                ]
            ]);

            Log::info("Added tag '{$tag}' to order {$orderId}");
        } catch (\Exception $e) {
            Log::error("Failed to add order tag", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
