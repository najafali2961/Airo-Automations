<?php

return [
    'categories' => [
        'Orders',
        'Products',
        'Customers',
        'Fulfillments',
        'Checkouts',
        'Carts',
        'Inventory',
        'Refunds',
        'Disputes',
        'Draft Orders',
        'Collections',
        'Discounts',
        'Themes',
        'Shop',
        'App',
    ],

    'triggers' => [
        // Orders
        [
            'category' => 'Orders',
            'list' => [
                ['label' => 'Order Created', 'value' => 'orders/create', 'description' => 'Triggers when a new order is placed.'],
                ['label' => 'Order Updated', 'value' => 'orders/update', 'description' => 'Triggers when an order is modified.'],
                ['label' => 'Order Cancelled', 'value' => 'orders/cancelled', 'description' => 'Triggers when an order is cancelled.'],
                ['label' => 'Order Fulfilled', 'value' => 'orders/fulfilled', 'description' => 'Triggers when an order is fully fulfilled.'],
                ['label' => 'Order Paid', 'value' => 'orders/paid', 'description' => 'Triggers when an order is marked as paid.'],
                ['label' => 'Order Partially Fulfilled', 'value' => 'orders/partially_fulfilled', 'description' => 'Triggers when an order is partially fulfilled.'],
                ['label' => 'Order Deleted', 'value' => 'orders/delete', 'description' => 'Triggers when an order is deleted.'],
            ]
        ],
        // Products
        [
            'category' => 'Products',
            'list' => [
                ['label' => 'Product Created', 'value' => 'products/create', 'description' => 'Triggers when a product is created.'],
                ['label' => 'Product Updated', 'value' => 'products/update', 'description' => 'Triggers when a product is updated.'],
                ['label' => 'Product Deleted', 'value' => 'products/delete', 'description' => 'Triggers when a product is deleted.'],
                ['label' => 'Collection Created', 'value' => 'collections/create', 'description' => 'Triggers when a collection is created.'],
                ['label' => 'Collection Updated', 'value' => 'collections/update', 'description' => 'Triggers when a collection is updated.'],
                ['label' => 'Collection Deleted', 'value' => 'collections/delete', 'description' => 'Triggers when a collection is deleted.'],
            ]
        ],
        // Customers
        [
            'category' => 'Customers',
            'list' => [
                ['label' => 'Customer Created', 'value' => 'customers/create', 'description' => 'Triggers when a customer is registered.'],
                ['label' => 'Customer Updated', 'value' => 'customers/update', 'description' => 'Triggers when customer data is updated.'],
                ['label' => 'Customer Enabled', 'value' => 'customers/enable', 'description' => 'Triggers when a customer account is enabled.'],
                ['label' => 'Customer Disabled', 'value' => 'customers/disable', 'description' => 'Triggers when a customer account is disabled.'],
                ['label' => 'Customer Deleted', 'value' => 'customers/delete', 'description' => 'Triggers when a customer is deleted.'],
            ]
        ],
        // Fulfillments
        [
            'category' => 'Fulfillments',
            'list' => [
                ['label' => 'Fulfillment Created', 'value' => 'fulfillments/create', 'description' => 'Triggers when a fulfillment is created.'],
                ['label' => 'Fulfillment Updated', 'value' => 'fulfillments/update', 'description' => 'Triggers when a fulfillment is updated (e.g., tracking added).'],
            ]
        ],
        // Checkouts
        [
            'category' => 'Checkouts',
            'list' => [
                ['label' => 'Checkout Created', 'value' => 'checkouts/create', 'description' => 'Triggers when a checkout is created.'],
                ['label' => 'Checkout Updated', 'value' => 'checkouts/update', 'description' => 'Triggers when a checkout is updated.'],
                ['label' => 'Checkout Deleted', 'value' => 'checkouts/delete', 'description' => 'Triggers when a checkout is deleted.'],
            ]
        ],
        // Carts
        [
            'category' => 'Carts',
            'list' => [
                ['label' => 'Cart Created', 'value' => 'carts/create', 'description' => 'Triggers when a cart is created.'],
                ['label' => 'Cart Updated', 'value' => 'carts/update', 'description' => 'Triggers when a cart is updated.'],
            ]
        ],
        // Inventory
        [
            'category' => 'Inventory',
            'list' => [
                ['label' => 'Inventory Level Updated', 'value' => 'inventory_levels/update', 'description' => 'Triggers when inventory level changes.'],
                ['label' => 'Inventory Item Created', 'value' => 'inventory_items/create', 'description' => 'Triggers when an inventory item is created.'],
                ['label' => 'Inventory Item Updated', 'value' => 'inventory_items/update', 'description' => 'Triggers when an inventory item is updated.'],
                ['label' => 'Inventory Item Deleted', 'value' => 'inventory_items/delete', 'description' => 'Triggers when an inventory item is deleted.'],
            ]
        ],
        // Refunds
        [
            'category' => 'Refunds',
            'list' => [
                ['label' => 'Refund Created', 'value' => 'refunds/create', 'description' => 'Triggers when a refund is created.'],
            ]
        ],
        // Draft Orders
        [
            'category' => 'Draft Orders',
            'list' => [
                ['label' => 'Draft Order Created', 'value' => 'draft_orders/create', 'description' => 'Triggers when a draft order is created.'],
                ['label' => 'Draft Order Updated', 'value' => 'draft_orders/update', 'description' => 'Triggers when a draft order is updated.'],
                ['label' => 'Draft Order Deleted', 'value' => 'draft_orders/delete', 'description' => 'Triggers when a draft order is deleted.'],
            ]
        ],
         // Discounts
         [
            'category' => 'Discounts',
            'list' => [
                ['label' => 'Discount Created', 'value' => 'discounts/create', 'description' => 'Triggers when a discount is created.'],
                ['label' => 'Discount Updated', 'value' => 'discounts/update', 'description' => 'Triggers when a discount is updated.'],
                ['label' => 'Discount Deleted', 'value' => 'discounts/delete', 'description' => 'Triggers when a discount is deleted.'],
            ]
        ],
        // Shop
        [
            'category' => 'Shop',
            'list' => [
                ['label' => 'Shop Updated', 'value' => 'shop/update', 'description' => 'Triggers when shop details are updated.'],
            ]
        ],
        // App
        [
            'category' => 'App',
            'list' => [
                ['label' => 'App Uninstalled', 'value' => 'app/uninstalled', 'description' => 'Triggers when the app is uninstalled.'],
            ]
        ],
    ],

    'actions' => [
        [
            'category' => 'Shopify',
            'list' => [
                [
                    'label' => 'Add Order Tag',
                    'value' => 'add_order_tag',
                    'description' => 'Adds a tag to the order triggering the workflow.',
                    'settings' => [
                        ['type' => 'text', 'name' => 'tag', 'label' => 'Tag to Add', 'required' => true]
                    ]
                ],
                [
                    'label' => 'Add Product Tag',
                    'value' => 'add_product_tag',
                    'description' => 'Adds a tag to the product triggering the workflow.',
                    'settings' => [
                        ['type' => 'text', 'name' => 'tag', 'label' => 'Tag to Add', 'required' => true]
                    ]
                ],
                [
                    'label' => 'Remove Order Tag',
                    'value' => 'remove_order_tag',
                    'description' => 'Removes a tag from the order.',
                    'settings' => [
                        ['type' => 'text', 'name' => 'tag', 'label' => 'Tag to Remove', 'required' => true]
                    ]
                ],
                [
                    'label' => 'Add Customer Tag',
                    'value' => 'add_customer_tag',
                    'description' => 'Adds a tag to the customer.',
                    'settings' => [
                        ['type' => 'text', 'name' => 'tag', 'label' => 'Tag to Add', 'required' => true]
                    ]
                ],
                [
                    'label' => 'Archive Order',
                    'value' => 'archive_order',
                    'description' => 'Archives the order.',
                    'settings' => []
                ],
                [
                    'label' => 'Unarchive Order',
                    'value' => 'unarchive_order',
                    'description' => 'Unarchives the order.',
                    'settings' => []
                ],
                [
                    'label' => 'Cancel Order',
                    'value' => 'cancel_order',
                    'description' => 'Cancels the order.',
                    'settings' => []
                ],
            ]
        ],
        [
            'category' => 'Communication',
            'list' => [
                [
                    'label' => 'Send Email (Internal)',
                    'value' => 'send_email_internal',
                    'description' => 'Sends an email to the shop owner.',
                    'settings' => [
                        ['type' => 'text', 'name' => 'subject', 'label' => 'Subject', 'required' => true],
                        ['type' => 'textarea', 'name' => 'body', 'label' => 'Message Body', 'required' => true],
                    ]
                ],
                 [
                    'label' => 'Send HTTP Request',
                    'value' => 'http_request',
                    'description' => 'Sends a webhook / HTTP request to an external URL.',
                    'settings' => [
                        ['type' => 'select', 'name' => 'method', 'label' => 'Method', 'options' => ['GET', 'POST', 'PUT', 'DELETE'], 'required' => true],
                        ['type' => 'text', 'name' => 'url', 'label' => 'URL', 'required' => true],
                        ['type' => 'textarea', 'name' => 'body', 'label' => 'JSON Body (Optional)', 'required' => false],
                    ]
                ],
                 [
                    'label' => 'Log Output',
                    'value' => 'log_output',
                    'description' => 'Logs a message to the execution log (for debugging).',
                    'settings' => [
                        ['type' => 'text', 'name' => 'message', 'label' => 'Message', 'required' => true],
                    ]
                ],
            ]
        ]
    ]
];
