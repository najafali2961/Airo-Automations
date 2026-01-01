<?php

namespace App\Services\N8N;

class NodeDefinitions
{
    public static function getDefinitions()
    {
        return [
            'n8n-nodes-base.shopify' => [
                'properties' => [
                    [
                        'name' => 'resource',
                        'type' => 'options',
                        'options' => [
                            ['name' => 'Order', 'value' => 'order'],
                            ['name' => 'Product', 'value' => 'product'],
                            ['name' => 'Customer', 'value' => 'customer'],
                            ['name' => 'Inventory', 'value' => 'inventoryLine'],
                        ]
                    ],
                    [
                        'name' => 'operation',
                        'type' => 'options',
                        'displayOptions' => ['show' => ['resource' => ['order']]],
                        'options' => [
                            ['name' => 'Create', 'value' => 'create'],
                            ['name' => 'Update', 'value' => 'update'],
                            ['name' => 'Get', 'value' => 'get'],
                            ['name' => 'Get Many', 'value' => 'getAll'],
                            ['name' => 'Cancel', 'value' => 'cancel'],
                        ]
                    ],
                    [
                        'name' => 'operation',
                        'type' => 'options',
                        'displayOptions' => ['show' => ['resource' => ['product']]],
                        'options' => [
                            ['name' => 'Create', 'value' => 'create'],
                            ['name' => 'Update', 'value' => 'update'],
                            ['name' => 'Get', 'value' => 'get'],
                            ['name' => 'Get Many', 'value' => 'getAll'],
                            ['name' => 'Delete', 'value' => 'delete'],
                        ]
                    ],
                     [
                        'name' => 'operation',
                        'type' => 'options',
                        'displayOptions' => ['show' => ['resource' => ['customer']]],
                        'options' => [
                            ['name' => 'Create', 'value' => 'create'],
                            ['name' => 'Update', 'value' => 'update'],
                            ['name' => 'Get', 'value' => 'get'],
                            ['name' => 'Get Many', 'value' => 'getAll'],
                        ]
                    ]
                ]
            ],
            'n8n-nodes-base.slack' => [
                'properties' => [
                    [
                        'name' => 'resource',
                        'type' => 'options',
                        'options' => [
                            ['name' => 'Message', 'value' => 'message'],
                            ['name' => 'Channel', 'value' => 'channel'],
                            ['name' => 'User', 'value' => 'user'],
                            ['name' => 'File', 'value' => 'file'],
                        ]
                    ],
                    [
                        'name' => 'operation',
                        'type' => 'options',
                        'displayOptions' => ['show' => ['resource' => ['message']]],
                        'options' => [
                            ['name' => 'Post', 'value' => 'post'],
                            ['name' => 'Update', 'value' => 'update'],
                            ['name' => 'Delete', 'value' => 'delete'],
                            ['name' => 'Get Permalink', 'value' => 'getPermalink'],
                        ]
                    ],
                    [
                        'name' => 'operation',
                        'type' => 'options',
                        'displayOptions' => ['show' => ['resource' => ['channel']]],
                        'options' => [
                            ['name' => 'Create', 'value' => 'create'],
                            ['name' => 'Invite', 'value' => 'invite'],
                            ['name' => 'Leave', 'value' => 'leave'],
                            ['name' => 'Kick', 'value' => 'kick'],
                            ['name' => 'Rename', 'value' => 'rename'],
                        ]
                    ]
                ]
            ],
            'n8n-nodes-base.googleSheets' => [
                 'properties' => [
                    [
                        'name' => 'resource',
                        'type' => 'options',
                        'options' => [
                            ['name' => 'Sheet', 'value' => 'sheet'],
                        ]
                    ],
                    [
                        'name' => 'operation',
                        'type' => 'options',
                         'displayOptions' => ['show' => ['resource' => ['sheet']]],
                        'options' => [
                            ['name' => 'Append', 'value' => 'append'],
                            ['name' => 'Read', 'value' => 'read'],
                            ['name' => 'Update', 'value' => 'update'],
                            ['name' => 'Clear', 'value' => 'clear'],
                        ]
                    ]
                 ]
            ],
            'n8n-nodes-base.shopifyTrigger' => [
                 'group' => ['trigger'],
                 'properties' => [
                     [
                         'name' => 'topic', // Triggers use topic usually
                         'type' => 'options',
                          // Fake logic for frontend parser who looks for 'resource' and 'operation' usually
                          // But our frontend parser is generic.
                          // Wait, my sidebar parser looks specifically for 'resource' and 'operation'.
                          // Triggers are harder.
                          // I'll stick to a simple structure for triggers that the frontend can default to
                          // OR I can use 'defaults' property in Sidebar.
                          'options' => [] 
                     ]
                 ]
            ]
        ];
    }
}
