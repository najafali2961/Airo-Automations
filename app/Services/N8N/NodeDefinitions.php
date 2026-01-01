<?php

namespace App\Services\N8N;

class NodeDefinitions
{
    public static function getDefinitions()
    {
        return [
            'n8n-nodes-base.shopify' => [
                'credentials' => [
                    ['name' => 'shopifyApi', 'required' => true],
                ],
                'properties' => [
                    [
                        'displayName' => 'Resource',
                        'name' => 'resource',
                        'type' => 'options',
                        'options' => [
                            ['name' => 'Order', 'value' => 'order'],
                            ['name' => 'Product', 'value' => 'product'],
                            ['name' => 'Customer', 'value' => 'customer'],
                            ['name' => 'Inventory', 'value' => 'inventoryLine'],
                        ],
                        'default' => 'order',
                    ],
                    [
                        'displayName' => 'Operation',
                        'name' => 'operation',
                        'type' => 'options',
                        'displayOptions' => ['show' => ['resource' => ['order']]],
                        'options' => [
                            ['name' => 'Create', 'value' => 'create'],
                            ['name' => 'Update', 'value' => 'update'],
                            ['name' => 'Get', 'value' => 'get'],
                            ['name' => 'Get Many', 'value' => 'getAll'],
                            ['name' => 'Cancel', 'value' => 'cancel'],
                        ],
                        'default' => 'create',
                    ],
                    [
                        'displayName' => 'Operation',
                        'name' => 'operation',
                        'type' => 'options',
                        'displayOptions' => ['show' => ['resource' => ['product']]],
                        'options' => [
                            ['name' => 'Create', 'value' => 'create'],
                            ['name' => 'Update', 'value' => 'update'],
                            ['name' => 'Get', 'value' => 'get'],
                            ['name' => 'Get Many', 'value' => 'getAll'],
                            ['name' => 'Delete', 'value' => 'delete'],
                        ],
                        'default' => 'create',
                    ],
                    // -- Product: Create Fields --
                    [
                        'displayName' => 'Title',
                        'name' => 'title',
                        'type' => 'string',
                        'default' => '',
                        'placeholder' => 'e.g. Awesome T-Shirt',
                        'displayOptions' => [
                            'show' => [
                                'resource' => ['product'],
                                'operation' => ['create'],
                            ],
                        ],
                    ],
                    [
                        'displayName' => 'Body HTML',
                        'name' => 'body_html',
                        'type' => 'string',
                        'typeOptions' => ['rows' => 4],
                        'default' => '',
                        'displayOptions' => [
                            'show' => [
                                'resource' => ['product'],
                                'operation' => ['create', 'update'],
                            ],
                        ],
                    ],
                    [
                        'displayName' => 'Vendor',
                        'name' => 'vendor',
                        'type' => 'string',
                        'default' => '',
                        'displayOptions' => [
                            'show' => [
                                'resource' => ['product'],
                                'operation' => ['create', 'update'],
                            ],
                        ],
                    ],
                    [
                        'displayName' => 'Product Type',
                        'name' => 'product_type',
                        'type' => 'string',
                        'default' => '',
                        'displayOptions' => [
                            'show' => [
                                'resource' => ['product'],
                                'operation' => ['create', 'update'],
                            ],
                        ],
                    ],
                    [
                        'displayName' => 'Tags',
                        'name' => 'tags',
                        'type' => 'string',
                        'default' => '',
                        'placeholder' => 'tag1, tag2',
                        'displayOptions' => [
                            'show' => [
                                'resource' => ['product'],
                                'operation' => ['create', 'update'],
                            ],
                        ],
                    ],
                    // -- End Product Create Fields --

                     [
                        'displayName' => 'Operation',
                        'name' => 'operation',
                        'type' => 'options',
                        'displayOptions' => ['show' => ['resource' => ['customer']]],
                        'options' => [
                            ['name' => 'Create', 'value' => 'create'],
                            ['name' => 'Update', 'value' => 'update'],
                            ['name' => 'Get', 'value' => 'get'],
                            ['name' => 'Get Many', 'value' => 'getAll'],
                        ],
                        'default' => 'create',
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
                 'credentials' => [
                    ['name' => 'shopifyApi', 'required' => true],
                 ],
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
