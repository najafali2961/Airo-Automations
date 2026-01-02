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
        // Create Execution Record
        $this->execution = Execution::create([
            'flow_id' => $flow->id,
            'event' => $topic,
            'external_event_id' => $externalEventId,
            'payload' => $payload,
            'status' => 'running',
            'nodes_executed' => 0
        ]);

        $this->log(null, 'info', "Starting workflow execution for event: {$topic}", ['external_event_id' => $externalEventId]);

        try {
            // Find Trigger Node
            $triggerNode = $flow->nodes()
                ->where('type', 'trigger')
                ->whereJsonContains('settings->topic', $topic)
                ->first();

            if (!$triggerNode) {
                 $this->log(null, 'warning', "No specific trigger node found for topic: {$topic}. Searching for generic trigger.");
                 $triggerNode = $flow->nodes()->where('type', 'trigger')->first();
            }

            if ($triggerNode) {
                $this->log($triggerNode->id, 'info', "Trigger matched: {$triggerNode->label}");
                $this->runNode($triggerNode, $payload);
            } else {
                $this->log(null, 'error', "No trigger node found in workflow.");
                throw new \Exception("Workflow has no trigger node.");
            }

            $this->execution->update(['status' => 'success']);
            $this->log(null, 'info', "Workflow execution completed successfully.");

        } catch (\Exception $e) {
            Log::error("Flow Execution Failed: " . $e->getMessage());
            $this->log(null, 'error', "Flow Execution Failed: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->execution->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
        }
    }

    protected function log($nodeId, $level, $message, $data = null)
    {
        try {
            Log::info("Attempting to log: {$message}", ['node_id' => $nodeId, 'execution_id' => $this->execution->id]);
            $this->execution->logs()->create([
                'node_id' => $nodeId,
                'level' => $level,
                'message' => $message,
                'data' => $data
            ]);
            Log::info("Log created successfully.");
        } catch (\Exception $e) {
            Log::error("CRITICAL: Failed to create ExecutionLog: " . $e->getMessage());
        }
    }

    protected function runNode(Node $node, array $data)
    {
        $this->log($node->id, 'info', "Executing node: {$node->label} ({$node->type})");
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
                    $label = $result ? 'true' : 'false';
                    $this->log($node->id, 'info', "Condition evaluated to: " . ($result ? 'TRUE' : 'FALSE'), ['result' => $result]);
                    $nextNodes = $node->nextNodes($label);
                    if ($nextNodes->isEmpty()) {
                        $nextNodes = $node->nextNodes('then');
                    }
                    break;

                case 'action':
                    $this->executeAction($node, $data);
                    $this->execution->increment('actions_completed');
                    $nextNodes = $node->nextNodes('then');
                    break;
            }

            // Recursive Call
            if ($nextNodes->isNotEmpty()) {
                $this->log($node->id, 'info', "Moving to next nodes: " . $nextNodes->pluck('label')->implode(', '));
                foreach ($nextNodes as $nextNode) {
                    $this->runNode($nextNode, $data);
                }
            } else {
                $this->log($node->id, 'info', "No sequence matching for this node branch.");
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

    protected function executeAction(Node $node, array $data)
    {
        $settings = $node->settings;
        $action = $settings['action'] ?? null;
        $form = $settings['form'] ?? [];

        if (!$action) {
            $this->log($node->id, 'warning', "Action node has no action type configured.");
            return;
        }

        $this->log($node->id, 'info', "Starting action: {$action}"); // Simplified Action Dispatcher
        switch ($action) {
            case 'log_output':
                Log::info("Workflow Log: " . ($form['message'] ?? ''));
                $this->log($node->id, 'info', "Output logged to Laravel logs: " . ($form['message'] ?? ''));
                break;
            
            case 'http_request':
                $method = $form['method'] ?? 'GET';
                $url = $form['url'] ?? '';
                if ($url) {
                    $this->log($node->id, 'info', "Sending {$method} request to {$url}");
                    $response = Http::withHeaders(['Content-Type' => 'application/json'])
                        ->$method($url, json_decode($form['body'] ?? '{}', true));
                    $this->log($node->id, 'info', "HTTP Response received", ['status' => $response->status(), 'body' => $response->json()]);
                }
                break;
                
            case 'add_product_tag':
                $this->addProductTag($data, $form, $node->id);
                break;
                
            case 'add_order_tag':
                $this->addOrderTag($data, $form, $node->id);
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

    protected function addProductTag($data, $form, $nodeId = null)
    {
        try {
            $productId = $data['id'] ?? $data['admin_graphql_api_id'] ?? null;
            $tag = $form['tag'] ?? null;
            
            if (!$productId) {
                $this->log($nodeId, 'error', "Missing product ID in payload", ['payload' => $data]);
                throw new \Exception("Missing product ID in payload");
            }
            if (!$tag) {
                $this->log($nodeId, 'warning', "No tag specified in action settings");
                return;
            }

            // Extract numeric ID if GID
            if (is_string($productId) && strpos($productId, 'gid://') === 0) {
                $productId = (int) basename($productId);
            }

            // Get shop from execution context
            $flow = $this->execution->flow;
            $userModel = config('auth.providers.users.model');
            $shop = $userModel::find($flow->shop_id);

            if (!$shop) {
                throw new \Exception("Shop not found for flow execution");
            }

            $this->log($nodeId, 'info', "Fetching product details from Shopify...", ['product_id' => $productId]);

            $apiVersion = config('shopify-app.api_version', '2024-07');
            $response = $shop->api()->rest('GET', "/admin/api/{$apiVersion}/products/{$productId}.json");
            
            if ($response['errors']) {
                $this->log($nodeId, 'error', "Shopify API Error (GET Product)", ['errors' => $response['errors']]);
                throw new \Exception("Failed to fetch product: " . json_encode($response['errors']));
            }

            $product = $response['body']['product'] ?? null;
            
            if (!$product) {
                $this->log($nodeId, 'error', "Product not found in Shopify", ['product_id' => $productId]);
                throw new \Exception("Product not found in Shopify.");
            }

            $currentTags = $product['tags'] ?? '';
            $tagsArray = array_filter(array_map('trim', explode(',', $currentTags)));
            
            $this->log($nodeId, 'info', "Current tags: " . ($currentTags ?: '(none)'));

            // Add new tag if not exists
            if (!in_array($tag, $tagsArray)) {
                $tagsArray[] = $tag;
                $this->log($nodeId, 'info', "Adding tag: {$tag}");
            } else {
                $this->log($nodeId, 'info', "Tag '{$tag}' already exists. skipping update.");
                return;
            }

            // Update product with new tags
            $updateResponse = $shop->api()->rest('PUT', "/admin/api/{$apiVersion}/products/{$productId}.json", [
                'product' => [
                    'id' => $productId,
                    'tags' => implode(', ', $tagsArray)
                ]
            ]);

            if ($updateResponse['errors']) {
                $this->log($nodeId, 'error', "Shopify API Error (PUT Product)", ['errors' => $updateResponse['errors']]);
                throw new \Exception("Failed to update product: " . json_encode($updateResponse['errors']));
            }

            $this->log($nodeId, 'info', "Successfully added tag '{$tag}' to product {$productId}");
        } catch (\Exception $e) {
            Log::error("Failed to add product tag: " . $e->getMessage());
            $this->log($nodeId, 'error', "Action Failed: " . $e->getMessage());
            throw $e;
        }
    }

    protected function addOrderTag($data, $form, $nodeId = null)
    {
        try {
            $orderId = $data['id'] ?? $data['admin_graphql_api_id'] ?? null;
            $tag = $form['tag'] ?? null;
            
            if (!$orderId) {
                $this->log($nodeId, 'error', "Missing order ID in payload", ['payload' => $data]);
                throw new \Exception("Missing order ID in payload");
            }
            if (!$tag) {
                $this->log($nodeId, 'warning', "No tag specified in action settings");
                return;
            }

            // Extract numeric ID if GID
            if (is_string($orderId) && strpos($orderId, 'gid://') === 0) {
                $orderId = (int) basename($orderId);
            }

            // Get shop from execution context
            $flow = $this->execution->flow;
            $userModel = config('auth.providers.users.model');
            $shop = $userModel::find($flow->shop_id);

            if (!$shop) {
                throw new \Exception("Shop not found for flow execution");
            }

            $apiVersion = config('shopify-app.api_version', '2024-07');
            $response = $shop->api()->rest('GET', "/admin/api/{$apiVersion}/orders/{$orderId}.json");
            
            if ($response['errors']) {
                $this->log($nodeId, 'error', "Shopify API Error (GET Order)", ['errors' => $response['errors']]);
                throw new \Exception("Failed to fetch order: " . json_encode($response['errors']));
            }

            $order = $response['body']['order'] ?? null;
            
            if (!$order) {
                $this->log($nodeId, 'error', "Order not found in Shopify", ['order_id' => $orderId]);
                throw new \Exception("Order not found in Shopify.");
            }

            $currentTags = $order['tags'] ?? '';
            $tagsArray = array_filter(array_map('trim', explode(',', $currentTags)));
            
            $this->log($nodeId, 'info', "Current tags: " . ($currentTags ?: '(none)'));

            // Add new tag if not exists
            if (!in_array($tag, $tagsArray)) {
                $tagsArray[] = $tag;
                $this->log($nodeId, 'info', "Adding tag: {$tag}");
            } else {
                $this->log($nodeId, 'info', "Tag '{$tag}' already exists. skipping update.");
                return;
            }

            // Update order with new tags
            $updateResponse = $shop->api()->rest('PUT', "/admin/api/{$apiVersion}/orders/{$orderId}.json", [
                'order' => [
                    'id' => $orderId,
                    'tags' => implode(', ', $tagsArray)
                ]
            ]);

            if ($updateResponse['errors']) {
                $this->log($nodeId, 'error', "Shopify API Error (PUT Order)", ['errors' => $updateResponse['errors']]);
                throw new \Exception("Failed to update order: " . json_encode($updateResponse['errors']));
            }

            $this->log($nodeId, 'info', "Successfully added tag '{$tag}' to order {$orderId}");
        } catch (\Exception $e) {
            Log::error("Failed to add order tag: " . $e->getMessage());
            $this->log($nodeId, 'error', "Action Failed: " . $e->getMessage());
            throw $e;
        }
    }
}
