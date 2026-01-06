<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shopify Triggers Configuration
    |--------------------------------------------------------------------------
    |
    | Define all available triggers for the workflow builder.
    | Each trigger maps to a Shopify webhook topic.
    |
    */
    'triggers' => [
        // Orders
        [
            'key' => 'orders_create',
            'label' => 'Order Created',
            'description' => 'Starts when an order is created',
            'topic' => 'ORDERS_CREATE',
            'category' => 'orders',
            'icon' => 'ShoppingBag',
        ],
        [
            'key' => 'orders_updated',
            'label' => 'Order Updated',
            'description' => 'Starts when an order is updated',
            'topic' => 'ORDERS_UPDATED',
            'category' => 'orders',
            'icon' => 'ShoppingBag',
        ],
        [
            'key' => 'orders_paid',
            'label' => 'Order Paid',
            'description' => 'Starts when an order is processed',
            'topic' => 'ORDERS_PAID',
            'category' => 'orders',
            'icon' => 'ShoppingBag',
        ],
        [
            'key' => 'orders_cancelled',
            'label' => 'Order Canceled',
            'description' => 'Starts when an order is canceled',
            'topic' => 'ORDERS_CANCELLED',
            'category' => 'orders',
            'icon' => 'ShoppingBag',
        ],
        [
            'key' => 'orders_fulfilled',
            'label' => 'Order Fulfilled',
            'description' => 'Starts when an order is prepared for shipment',
            'topic' => 'ORDERS_FULFILLED',
            'category' => 'orders',
            'icon' => 'ShoppingBag',
        ],
        [
            'key' => 'orders_partially_fulfilled',
            'label' => 'Order Partially Fulfilled',
            'description' => 'Starts when a new partial order fulfillment is created',
            'topic' => 'ORDERS_PARTIALLY_FULFILLED',
            'category' => 'orders',
            'icon' => 'ShoppingBag',
        ],
        
        // Products
        [
            'key' => 'products_create',
            'label' => 'Product Created',
            'description' => 'Starts when a product is created',
            'topic' => 'PRODUCTS_CREATE',
            'category' => 'products',
            'icon' => 'Package',
        ],
        [
            'key' => 'products_update',
            'label' => 'Product Updated',
            'description' => 'Starts when a product is updated',
            'topic' => 'PRODUCTS_UPDATE',
            'category' => 'products',
            'icon' => 'Package',
        ],
        [
            'key' => 'products_delete',
            'label' => 'Product Deleted',
            'description' => 'Starts when a product is removed',
            'topic' => 'PRODUCTS_DELETE',
            'category' => 'products',
            'icon' => 'Package',
        ],
        
        // Customers
        [
            'key' => 'customers_create',
            'label' => 'Customer Created',
            'description' => 'Starts when a customer is created',
            'topic' => 'CUSTOMERS_CREATE',
            'category' => 'customers',
            'icon' => 'Users',
        ],
        [
            'key' => 'customers_update',
            'label' => 'Customer Updated',
            'description' => 'Starts when a customer is updated',
            'topic' => 'CUSTOMERS_UPDATE',
            'category' => 'customers',
            'icon' => 'Users',
        ],
        [
            'key' => 'customers_delete',
            'label' => 'Customer Deleted',
            'description' => 'Starts when a customer is removed',
            'topic' => 'CUSTOMERS_DELETE',
            'category' => 'customers',
            'icon' => 'Users',
        ],
        
        // Collections
        [
            'key' => 'collections_create',
            'label' => 'Collection Created',
            'description' => 'Starts when a collection is created',
            'topic' => 'COLLECTIONS_CREATE',
            'category' => 'collections',
            'icon' => 'Grid',
        ],
        [
            'key' => 'collections_update',
            'label' => 'Collection Updated',
            'description' => 'Starts when a collection is updated',
            'topic' => 'COLLECTIONS_UPDATE',
            'category' => 'collections',
            'icon' => 'Grid',
        ],
        [
            'key' => 'collections_delete',
            'label' => 'Collection Deleted',
            'description' => 'Starts when a collection is removed',
            'topic' => 'COLLECTIONS_DELETE',
            'category' => 'collections',
            'icon' => 'Grid',
        ],
        
        // Fulfillments
        [
            'key' => 'fulfillments_create',
            'label' => 'Fulfillment Created',
            'description' => 'Starts when a fulfillment is created',
            'topic' => 'FULFILLMENTS_CREATE',
            'category' => 'fulfillments',
            'icon' => 'Truck',
        ],
        [
            'key' => 'fulfillments_update',
            'label' => 'Fulfillment Updated',
            'description' => 'Starts when a fulfillment is updated',
            'topic' => 'FULFILLMENTS_UPDATE',
            'category' => 'fulfillments',
            'icon' => 'Truck',
        ],

        // Draft Orders
        [
            'key' => 'draft_orders_create',
            'label' => 'Draft Order Created',
            'description' => 'Starts when a draft order is created',
            'topic' => 'DRAFT_ORDERS_CREATE',
            'category' => 'draft_orders',
            'icon' => 'FileText',
        ],
        [
            'key' => 'draft_orders_updated',
            'label' => 'Draft Order Updated',
            'description' => 'Starts when a draft order is updated',
            'topic' => 'DRAFT_ORDERS_UPDATED',
            'category' => 'draft_orders',
            'icon' => 'FileText',
        ],

        // Refunds
        [
            'key' => 'refunds_create',
            'label' => 'Refund Created',
            'description' => 'Starts when a refund is created',
            'topic' => 'REFUNDS_CREATE',
            'category' => 'refunds',
            'icon' => 'ArrowBack',
        ],

        // Shop
        [
            'key' => 'shop_update',
            'label' => 'Shop Updated',
            'description' => 'Starts when a shop is updated',
            'topic' => 'SHOP_UPDATE',
            'category' => 'shop',
            'icon' => 'Store',
        ],

        // Discounts
        [
            'key' => 'discounts_create',
            'label' => 'Discount Created',
            'description' => 'Starts when a discount is created',
            'topic' => 'DISCOUNTS_CREATE',
            'category' => 'discounts',
            'icon' => 'Tag',
        ],
        [
            'key' => 'discounts_update',
            'label' => 'Discount Updated',
            'description' => 'Starts when a discount is updated',
            'topic' => 'DISCOUNTS_UPDATE',
            'category' => 'discounts',
            'icon' => 'Tag',
        ],

        // Inventory
        [
            'key' => 'inventory_levels_update',
            'label' => 'Inventory Updated',
            'description' => 'Starts when an inventory level is updated',
            'topic' => 'INVENTORY_LEVELS_UPDATE',
            'category' => 'inventory',
            'icon' => 'Database',
        ],

        // Checkouts
        [
            'key' => 'checkouts_create',
            'label' => 'Checkout Created',
            'description' => 'Starts when a checkout is created',
            'topic' => 'CHECKOUTS_CREATE',
            'category' => 'checkouts',
            'icon' => 'CreditCard',
        ],
        [
            'key' => 'checkouts_update',
            'label' => 'Checkout Updated',
            'description' => 'Starts when a checkout is updated',
            'topic' => 'CHECKOUTS_UPDATE',
            'category' => 'checkouts',
            'icon' => 'CreditCard',
        ],

        // Carts
        [
            'key' => 'carts_create',
            'label' => 'Cart Created',
            'description' => 'Starts when a cart is created',
            'topic' => 'CARTS_CREATE',
            'category' => 'carts',
            'icon' => 'ShoppingCart',
        ],

        // App
        [
            'key' => 'app_subscriptions_update',
            'label' => 'App Subscription Updated',
            'description' => 'Starts when an app subscription is updated',
            'topic' => 'APP_SUBSCRIPTIONS_UPDATE',
            'category' => 'app',
            'icon' => 'AppWindow',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Shopify Actions Configuration
    |--------------------------------------------------------------------------
    |
    | Define all available actions for the workflow builder.
    |
    */
    'actions' => [
        // Order Actions
        [
            'key' => 'add_order_tag',
            'label' => 'Add Order Tag',
            'description' => 'Add tags to a Shopify order',
            'category' => 'orders',
            'icon' => 'Tag',
            'fields' => [['name' => 'tags', 'label' => 'Tags', 'type' => 'text', 'placeholder' => 'VIP, Urgent', 'required' => true]],
        ],
        [
            'key' => 'remove_order_tag',
            'label' => 'Remove Order Tag',
            'description' => 'Remove tags from a Shopify order',
            'category' => 'orders',
            'icon' => 'Tag',
            'fields' => [['name' => 'tags', 'label' => 'Tags', 'type' => 'text', 'placeholder' => 'OldTag', 'required' => true]],
        ],
        [
            'key' => 'cancel_order',
            'label' => 'Cancel Order',
            'description' => 'Cancel a Shopify order',
            'category' => 'orders',
            'icon' => 'X',
            'fields' => [
                ['name' => 'reason', 'label' => 'Reason', 'type' => 'select', 'options' => [
                    ['value' => 'customer', 'label' => 'Customer'],
                    ['value' => 'inventory', 'label' => 'Inventory'],
                    ['value' => 'fraud', 'label' => 'Fraud'],
                    ['value' => 'declined', 'label' => 'Declined'],
                    ['value' => 'other', 'label' => 'Other'],
                ], 'default' => 'other'],
                ['name' => 'note', 'label' => 'Note', 'type' => 'textarea', 'placeholder' => 'Cancellation note'],
            ],
        ],
        [
            'key' => 'archive_order',
            'label' => 'Archive Order',
            'description' => 'Close / archive a Shopify order',
            'category' => 'orders',
            'icon' => 'Archive',
            'fields' => [],
        ],
        [
            'key' => 'unarchive_order',
            'label' => 'Unarchive Order',
            'description' => 'Reopen / unarchive a Shopify order',
            'category' => 'orders',
            'icon' => 'ArrowUp',
            'fields' => [],
        ],
        [
            'key' => 'hold_fulfillment',
            'label' => 'Hold Fulfillment',
            'description' => 'Place order fulfillment on hold',
            'category' => 'orders',
            'icon' => 'Pause',
            'fields' => [['name' => 'reason', 'label' => 'Reason', 'type' => 'text', 'required' => true]],
        ],
        [
            'key' => 'capture_payment',
            'label' => 'Capture Payment',
            'description' => 'Capture authorized payment',
            'category' => 'orders',
            'icon' => 'DollarSign',
            'fields' => [],
        ],

        // Product Actions
        [
            'key' => 'add_product_tag',
            'label' => 'Add Product Tag',
            'description' => 'Add tags to a Shopify product',
            'category' => 'products',
            'icon' => 'Tag',
            'fields' => [['name' => 'tags', 'label' => 'Tags', 'type' => 'text', 'placeholder' => 'New Arrival', 'required' => true]],
        ],
        [
            'key' => 'remove_product_tag',
            'label' => 'Remove Product Tag',
            'description' => 'Remove tags from a Shopify product',
            'category' => 'products',
            'icon' => 'Tag',
            'fields' => [['name' => 'tags', 'label' => 'Tags', 'type' => 'text', 'required' => true]],
        ],
        [
            'key' => 'update_product_status',
            'label' => 'Update Product Status',
            'description' => 'Change product status',
            'category' => 'products',
            'icon' => 'Settings',
            'fields' => [
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'draft', 'label' => 'Draft'],
                    ['value' => 'archived', 'label' => 'Archived'],
                ], 'required' => true],
            ],
        ],
        [
            'key' => 'delete_product',
            'label' => 'Delete Product',
            'description' => 'Permanently delete a product',
            'category' => 'products',
            'icon' => 'Trash',
            'fields' => [],
        ],

        // Customer Actions
        [
            'key' => 'add_customer_tag',
            'label' => 'Add Customer Tag',
            'description' => 'Add tags to a Shopify customer',
            'category' => 'customers',
            'icon' => 'Tag',
            'fields' => [['name' => 'tags', 'label' => 'Tags', 'type' => 'text', 'placeholder' => 'Newsletter', 'required' => true]],
        ],
        [
            'key' => 'remove_customer_tag',
            'label' => 'Remove Customer Tag',
            'description' => 'Remove tags from a Shopify customer',
            'category' => 'customers',
            'icon' => 'Tag',
            'fields' => [['name' => 'tags', 'label' => 'Tags', 'type' => 'text', 'required' => true]],
        ],
        [
            'key' => 'enable_customer',
            'label' => 'Enable Customer',
            'description' => 'Enable customer account',
            'category' => 'customers',
            'icon' => 'UserCheck',
            'fields' => [],
        ],
        [
            'key' => 'disable_customer',
            'label' => 'Disable Customer',
            'description' => 'Disable customer account',
            'category' => 'customers',
            'icon' => 'UserX',
            'fields' => [],
        ],

        // Collection Actions
        [
            'key' => 'add_to_collection',
            'label' => 'Add to Collection',
            'description' => 'Add product to a collection',
            'category' => 'collections',
            'icon' => 'PlusSquare',
            'fields' => [['name' => 'collection_id', 'label' => 'Collection ID', 'type' => 'text', 'required' => true]],
        ],

        // System Actions
        [
            'key' => 'http_request',
            'label' => 'Send HTTP Request',
            'description' => 'Send a custom HTTP request',
            'category' => 'system',
            'icon' => 'ExternalLink',
            'fields' => [
                ['name' => 'method', 'label' => 'Method', 'type' => 'select', 'options' => [
                    ['value' => 'GET', 'label' => 'GET'],
                    ['value' => 'POST', 'label' => 'POST'],
                    ['value' => 'PUT', 'label' => 'PUT'],
                ], 'default' => 'POST'],
                ['name' => 'url', 'label' => 'URL', 'type' => 'text', 'required' => true],
                ['name' => 'body', 'label' => 'Body (JSON)', 'type' => 'textarea'],
            ],
        ],
        [
            'key' => 'send_webhook',
            'label' => 'Send Webhook',
            'description' => 'Trigger an external webhook (e.g. n8n)',
            'category' => 'system',
            'icon' => 'Zap',
            'fields' => [['name' => 'url', 'label' => 'Webhook URL', 'type' => 'text', 'required' => true]],
        ],
        [
            'key' => 'log_output',
            'label' => 'Log Message',
            'description' => 'Log a message to the execution trace',
            'category' => 'system',
            'icon' => 'FileText',
            'fields' => [['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => true]],
        ],
        
        // Generic / Advanced
        [
            'key' => 'add_tag',
            'label' => 'Add Tag (Auto-detect)',
            'description' => 'Automatically add tags based on trigger context',
            'category' => 'generic',
            'icon' => 'Tag',
            'fields' => [['name' => 'tags', 'label' => 'Tags', 'type' => 'text', 'placeholder' => 'Tag1, Tag2', 'required' => true]],
        ],
        [
            'key' => 'custom_code',
            'label' => 'Custom Code (JS)',
            'description' => 'Run custom JavaScript logic',
            'category' => 'advanced',
            'icon' => 'Code',
            'fields' => [['name' => 'code', 'label' => 'JavaScript', 'type' => 'textarea', 'placeholder' => '// write your code here', 'required' => true]],
        ],

        // Inventory Actions
        [
            'key' => 'adjust_inventory',
            'label' => 'Adjust Inventory',
            'description' => 'Adjust inventory quantity for a variant',
            'category' => 'inventory',
            'icon' => 'PlusCircle',
            'fields' => [
                ['name' => 'inventory_item_id', 'label' => 'Inventory Item ID', 'type' => 'text', 'required' => true],
                ['name' => 'location_id', 'label' => 'Location ID', 'type' => 'text', 'required' => true],
                ['name' => 'delta', 'label' => 'Adjustment Amount', 'type' => 'number', 'required' => true, 'placeholder' => 'e.g. -1 or 5'],
            ],
        ],

        // Discount Actions
        [
            'key' => 'create_basic_discount',
            'label' => 'Create Basic Discount',
            'description' => 'Create a simple percentage/amount discount',
            'category' => 'discounts',
            'icon' => 'Tag',
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true, 'placeholder' => 'Summer Sale'],
                ['name' => 'code', 'label' => 'Code', 'type' => 'text', 'required' => true, 'placeholder' => 'SUMMER20'],
                ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'required' => true],
                ['name' => 'value_type', 'label' => 'Value Type', 'type' => 'select', 'options' => [
                    ['value' => 'percentage', 'label' => 'Percentage'],
                    ['value' => 'fixed_amount', 'label' => 'Fixed Amount'],
                ], 'default' => 'percentage'],
            ],
        ],

        // Product Pricing
        [
            'key' => 'update_variant_price',
            'label' => 'Update Variant Price',
            'description' => 'Update the price of a product variant',
            'category' => 'products',
            'icon' => 'DollarSign',
            'fields' => [
                ['name' => 'variant_id', 'label' => 'Variant ID', 'type' => 'text', 'required' => true],
                ['name' => 'price', 'label' => 'New Price', 'type' => 'number', 'required' => true],
            ],
        ],


        // Google Actions
        [
            'key' => 'send_gmail',
            'label' => 'Send Gmail',
            'description' => 'Send an email via Gmail',
            'category' => 'communication',
            'icon' => 'Mail',
            'app' => 'google',
            'fields' => [
                 ['name' => 'recipient_type', 'label' => 'Recipient Strategy', 'type' => 'select', 'options' => [
                    ['value' => 'custom', 'label' => 'Custom Email (Use "To" field)'],
                    ['value' => 'customer_email', 'label' => 'Customer Email (from Trigger)'],
                    ['value' => 'shop_email', 'label' => 'My Shop Email'],
                 ], 'default' => 'custom'],
                 ['name' => 'to', 'label' => 'To (Custom)', 'type' => 'text', 'placeholder' => 'example@email.com'],
                 ['name' => 'subject', 'label' => 'Subject', 'type' => 'text', 'required' => true],
                 ['name' => 'body', 'label' => 'Body (HTML)', 'type' => 'textarea', 'required' => true],
            ]
        ],
        [
            'key' => 'add_to_sheet',
            'label' => 'Add to Google Sheet',
            'description' => 'Append a row to a Google Sheet',
            'category' => 'productivity',
            'icon' => 'Table',
            'app' => 'google',
            'fields' => [
                 ['name' => 'spreadsheet_id', 'label' => 'Spreadsheet ID', 'type' => 'text', 'required' => true],
                 ['name' => 'range', 'label' => 'Range', 'type' => 'text', 'default' => 'Sheet1!A1'],
                 ['name' => 'values', 'label' => 'Values (Comma Separated)', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Val1, Val2, Val3'],
            ]
        ],
        [
            'key' => 'create_doc',
            'label' => 'Create Google Doc',
            'description' => 'Create a new Google Doc',
            'category' => 'productivity',
            'icon' => 'FileText',
            'app' => 'google',
            'fields' => [
                 ['name' => 'title_source', 'label' => 'Title Strategy', 'type' => 'select', 'options' => [
                    ['value' => 'custom', 'label' => 'Custom Title'],
                    ['value' => 'product_title', 'label' => 'Product Title (from Trigger)'],
                    ['value' => 'order_name', 'label' => 'Order Name (from Trigger)'],
                 ], 'default' => 'custom'],
                 ['name' => 'title', 'label' => 'Document Title (Custom)', 'type' => 'text'],
                 ['name' => 'content', 'label' => 'Content', 'type' => 'textarea'],
            ]
        ],
        [
            'key' => 'create_sheet',
            'label' => 'Create Google Sheet',
            'description' => 'Create a new Google Spreadsheet',
            'category' => 'productivity',
            'icon' => 'Table',
            'app' => 'google',
            'fields' => [
                 ['name' => 'title_source', 'label' => 'Title Strategy', 'type' => 'select', 'options' => [
                    ['value' => 'custom', 'label' => 'Custom Title'],
                    ['value' => 'product_title', 'label' => 'Product Title (from Trigger)'],
                    ['value' => 'order_name', 'label' => 'Order Name (from Trigger)'],
                 ], 'default' => 'custom'],
                 ['name' => 'title', 'label' => 'Sheet Title (Custom)', 'type' => 'text', 'placeholder' => 'Project {{ product.title }}'],
            ]
        ],
        [
            'key' => 'send_smart_email',
            'label' => 'Send Smart Email',
            'description' => 'Send an email with all trigger details automatically formatted.',
            'category' => 'communication',
            'icon' => 'Mail',
            'app' => 'google',
            'fields' => [
                 ['name' => 'to', 'label' => 'To', 'type' => 'text', 'required' => true],
                 ['name' => 'subject', 'label' => 'Subject', 'type' => 'text', 'required' => true, 'default' => 'New Event: {{ event }}'],
            ]
        ],
        // Google Drive Actions
        [
            'key' => 'create_folder',
            'label' => 'Create Drive Folder',
            'description' => 'Create a new folder in Google Drive',
            'category' => 'productivity',
            'icon' => 'Folder',
            'app' => 'google',
            'fields' => [
                 ['name' => 'name_source', 'label' => 'Name Strategy', 'type' => 'select', 'options' => [
                    ['value' => 'custom', 'label' => 'Custom Name'],
                    ['value' => 'order_name', 'label' => 'Order Name'],
                    ['value' => 'product_title', 'label' => 'Product Title'],
                    ['value' => 'customer_name', 'label' => 'Customer Name'],
                 ], 'default' => 'custom'],
                 ['name' => 'folder_name', 'label' => 'Folder Name (Custom)', 'type' => 'text', 'placeholder' => 'New Folder'],
            ]
        ],
        [
            'key' => 'upload_file',
            'label' => 'Upload File to Drive',
            'description' => 'Save a file from URL to Google Drive',
            'category' => 'productivity',
            'icon' => 'UploadCloud',
            'app' => 'google',
            'fields' => [
                 ['name' => 'file_url', 'label' => 'File URL', 'type' => 'text', 'required' => true, 'placeholder' => 'https://...'],
                 ['name' => 'file_name', 'label' => 'File Name', 'type' => 'text', 'required' => true],
                 ['name' => 'folder_id', 'label' => 'Parent Folder ID (Optional)', 'type' => 'text'],
            ]
        ],
        [
            'key' => 'create_text_file',
            'label' => 'Create Text File',
            'description' => 'Create a plain text file in Drive',
            'category' => 'productivity',
            'icon' => 'File',
            'app' => 'google',
            'fields' => [
                 ['name' => 'file_name', 'label' => 'File Name', 'type' => 'text', 'required' => true, 'placeholder' => 'log.txt'],
                 ['name' => 'content', 'label' => 'Content', 'type' => 'textarea', 'required' => true],
                 ['name' => 'folder_id', 'label' => 'Parent Folder ID (Optional)', 'type' => 'text'],
            ]
        ],
        // SMTP Actions
        [
            'key' => 'send_smtp_email',
            'label' => 'Send SMTP Email',
            'description' => 'Send an email using your custom SMTP server',
            'category' => 'communication',
            'icon' => 'Mail',
            'app' => 'smtp',
            'fields' => [
                 ['name' => 'recipient_type', 'label' => 'Recipient Strategy', 'type' => 'select', 'options' => [
                    ['value' => 'custom', 'label' => 'Custom Email (Use "To" field)'],
                    ['value' => 'customer_email', 'label' => 'Customer Email (from Trigger)'],
                 ], 'default' => 'custom'],
                 ['name' => 'to', 'label' => 'To (Custom)', 'type' => 'text', 'placeholder' => 'recipient@example.com'],
                 ['name' => 'subject', 'label' => 'Subject', 'type' => 'text', 'required' => true],
                 ['name' => 'body', 'label' => 'Body (HTML)', 'type' => 'textarea', 'required' => true],
            ]
        ],
    ],
];
