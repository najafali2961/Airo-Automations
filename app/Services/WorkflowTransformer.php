<?php

namespace App\Services;

class WorkflowTransformer
{
    /**
     * Convert React Flow UI data to N8N Workflow JSON
     *
     * @param array $uiData
     * @return array
     */
    public function toN8n(array $uiData): array
    {
        $nodes = $uiData['nodes'] ?? [];
        $edges = $uiData['edges'] ?? [];
        $workflowName = $uiData['name'] ?? 'Untitled Workflow';

        $n8nNodes = [];
        $connections = [];

        // 1. Transform Nodes
        foreach ($nodes as $node) {
            $n8nNode = $this->transformNode($node);
            if ($n8nNode) {
                // Determine N8N parameters based on type
                $n8nNodes[] = $n8nNode;
            }
        }

        // 2. Transform Connections (Edges)
        foreach ($edges as $edge) {
            $sourceId = $edge['source'];
            $targetId = $edge['target'];
            
            // Find corresponding N8N node names
            $sourceNodeName = $this->getNodeNameById($nodes, $sourceId);
            $targetNodeName = $this->getNodeNameById($nodes, $targetId);

            if ($sourceNodeName && $targetNodeName) {
                if (!isset($connections[$sourceNodeName])) {
                    $connections[$sourceNodeName] = ['main' => []];
                }
                
                // Ensure main array structure
                if (!isset($connections[$sourceNodeName]['main'][0])) {
                    $connections[$sourceNodeName]['main'][0] = [];
                }

                $connections[$sourceNodeName]['main'][0][] = [
                    'node' => $targetNodeName,
                    'type' => 'main',
                    'index' => 0
                ];
            }
        }

        return [
            'name' => $workflowName,
            'nodes' => $n8nNodes,
            'connections' => $connections,
        ];
    }

    private function transformNode($node)
    {
        $type = $node['type'] ?? 'unknown';
        $data = $node['data'] ?? [];
        $position = [$node['position']['x'], $node['position']['y']];
        $name = $data['label'] ?? $node['id'];
        
        // Ensure unique names if needed, but for now trust label or ID
        // Note: N8N requires unique names. We might need to enforce this or map IDs to names better.
        // Using label as Name for readability, but should fallback to ID if empty.
        
        $baseNode = [
            'name' => $name,
            'typeVersion' => 1,
            'position' => $position,
            'id' => $node['id'] // Keep ID for edge mapping, remove later if needed
        ];

        switch ($type) {
            case 'trigger': // Mapped from 'shopifyTrigger' or generic 'trigger'
            case 'shopifyTrigger':
                // Check subtype in data or config
                $triggerType = $data['config']['triggerType'] ?? $data['topic'] ?? 'orders/create';
                
                return array_merge($baseNode, [
                    'type' => 'n8n-nodes-base.webhook',
                    'parameters' => [
                        'httpMethod' => 'POST',
                        'path' => 'shopify-webhook-' . uniqid(), // Should be consistent or derived
                        'options' => []
                    ]
                ]);

            case 'action':
                $actionType = $data['config']['type'] ?? $data['type'] ?? 'send_email';
                $config = $data['config'] ?? [];

                if ($actionType === 'send_email') {
                    return array_merge($baseNode, [
                        'type' => 'n8n-nodes-base.emailSend', // Simplified for generic email
                        'parameters' => [
                            'fromEmail' => $config['from'] ?? '',
                            'toEmail' => $config['to'] ?? '',
                            'subject' => $config['subject'] ?? '',
                            'text' => $config['body'] ?? '',
                        ]
                    ]);
                } elseif ($actionType === 'send_slack') {
                    return array_merge($baseNode, [
                        'type' => 'n8n-nodes-base.slack',
                        'parameters' => [
                            'channel' => $config['channel'] ?? '',
                            'text' => $config['message'] ?? '',
                        ]
                    ]);
                }
                
                // Default fallback action
                return array_merge($baseNode, [
                    'type' => 'n8n-nodes-base.noOp',
                ]);

            case 'condition':
                 $config = $data['config'] ?? [];
                 return array_merge($baseNode, [
                    'type' => 'n8n-nodes-base.if',
                    'parameters' => [
                        'conditions' => [
                            'boolean' => [
                                [
                                    'value1' => $config['field'] ?? '',
                                    'value2' => $config['value'] ?? ''
                                ]
                            ]
                        ]
                    ]
                ]);

            default:
                return array_merge($baseNode, [
                    'type' => 'n8n-nodes-base.noOp',
                ]);
        }
    }

    private function getNodeNameById($nodes, $id)
    {
        foreach ($nodes as $node) {
            if ($node['id'] === $id) {
                return $node['data']['label'] ?? $node['id'];
            }
        }
        return null;
    }
}
