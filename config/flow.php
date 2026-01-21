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
            'variables' => [
                // Identity
                ['label' => 'Order Name (e.g. #1001)', 'value' => 'order.name'],
                ['label' => 'Order ID', 'value' => 'order.id'],
                ['label' => 'Order Number', 'value' => 'order.order_number'],
                ['label' => 'Processed At', 'value' => 'order.processed_at'],
                
                // Financials
                ['label' => 'Total Price', 'value' => 'order.total_price'],
                ['label' => 'Subtotal', 'value' => 'order.subtotal_price'],
                ['label' => 'Total Tax', 'value' => 'order.total_tax'],
                ['label' => 'Total Discounts', 'value' => 'order.total_discounts'],
                ['label' => 'Total Line Items Price', 'value' => 'order.total_line_items_price'],
                ['label' => 'Outstanding Balance', 'value' => 'order.total_outstanding'],
                ['label' => 'Currency', 'value' => 'order.currency'],
                ['label' => 'Financial Status', 'value' => 'order.financial_status'],
                ['label' => 'Payment Gateway Names', 'value' => 'order.payment_gateway_names.0'], // First gateway
                ['label' => 'Taxes Included', 'value' => 'order.taxes_included'],
                
                // Customer
                ['label' => 'Customer Email', 'value' => 'order.email'],
                ['label' => 'Customer Phone', 'value' => 'order.phone'],
                ['label' => 'Customer ID', 'value' => 'order.customer.id'],
                ['label' => 'Customer First Name', 'value' => 'order.customer.first_name'],
                ['label' => 'Customer Last Name', 'value' => 'order.customer.last_name'],
                ['label' => 'Customer Orders Count', 'value' => 'order.customer.orders_count'],
                ['label' => 'Customer Total Spent', 'value' => 'order.customer.total_spent'],
                ['label' => 'Customer Tags', 'value' => 'order.customer.tags'],
                ['label' => 'Customer Verified Email', 'value' => 'order.customer.verified_email'],
                ['label' => 'Customer Accepts Marketing', 'value' => 'order.customer.accepts_marketing'],

                // Addresses
                ['label' => 'Billing Name', 'value' => 'order.billing_address.name'],
                ['label' => 'Billing City', 'value' => 'order.billing_address.city'],
                ['label' => 'Billing Country', 'value' => 'order.billing_address.country'],
                ['label' => 'Billing Country Code', 'value' => 'order.billing_address.country_code'],
                ['label' => 'Billing Zip', 'value' => 'order.billing_address.zip'],
                ['label' => 'Billing Province', 'value' => 'order.billing_address.province'],
                
                ['label' => 'Shipping Name', 'value' => 'order.shipping_address.name'],
                ['label' => 'Shipping City', 'value' => 'order.shipping_address.city'],
                ['label' => 'Shipping Country', 'value' => 'order.shipping_address.country'],
                ['label' => 'Shipping Country Code', 'value' => 'order.shipping_address.country_code'],
                ['label' => 'Shipping Zip', 'value' => 'order.shipping_address.zip'],
                ['label' => 'Shipping Province', 'value' => 'order.shipping_address.province'],
                ['label' => 'Shipping Phone', 'value' => 'order.shipping_address.phone'],

                // Fulfillment
                ['label' => 'Fulfillment Status', 'value' => 'order.fulfillment_status'],
                ['label' => 'Shipping Lines Title', 'value' => 'order.shipping_lines.0.title'],
                ['label' => 'Shipping Lines Price', 'value' => 'order.shipping_lines.0.price'],
                
                // Meta
                ['label' => 'Tags', 'value' => 'order.tags'],
                ['label' => 'Note', 'value' => 'order.note'],
                ['label' => 'Referring Site', 'value' => 'order.referring_site'],
                ['label' => 'Landing Site', 'value' => 'order.landing_site'],
                ['label' => 'Cancel Reason', 'value' => 'order.cancel_reason'],
                ['label' => 'Buyer Accepts Marketing', 'value' => 'order.buyer_accepts_marketing'],
                ['label' => 'Test Order', 'value' => 'order.test'],
                ['label' => 'Validation Link', 'value' => 'order.order_status_url'],
            ],
        ],
        [
            'key' => 'orders_updated',
            'label' => 'Order Updated',
            'description' => 'Starts when an order is updated',
            'topic' => 'ORDERS_UPDATED',
            'category' => 'orders',
            'icon' => 'ShoppingBag',
            'variables' => [
                // Identity
                ['label' => 'Order Name (e.g. #1001)', 'value' => 'order.name'],
                ['label' => 'Order ID', 'value' => 'order.id'],
                ['label' => 'Order Number', 'value' => 'order.order_number'],
                ['label' => 'Processed At', 'value' => 'order.processed_at'],
                
                // Financials
                ['label' => 'Total Price', 'value' => 'order.total_price'],
                ['label' => 'Subtotal', 'value' => 'order.subtotal_price'],
                ['label' => 'Total Tax', 'value' => 'order.total_tax'],
                ['label' => 'Total Discounts', 'value' => 'order.total_discounts'],
                ['label' => 'Total Line Items Price', 'value' => 'order.total_line_items_price'],
                ['label' => 'Outstanding Balance', 'value' => 'order.total_outstanding'],
                ['label' => 'Currency', 'value' => 'order.currency'],
                ['label' => 'Financial Status', 'value' => 'order.financial_status'],
                ['label' => 'Payment Gateway Names', 'value' => 'order.payment_gateway_names.0'], // First gateway
                ['label' => 'Taxes Included', 'value' => 'order.taxes_included'],
                
                // Customer
                ['label' => 'Customer Email', 'value' => 'order.email'],
                ['label' => 'Customer Phone', 'value' => 'order.phone'],
                ['label' => 'Customer ID', 'value' => 'order.customer.id'],
                ['label' => 'Customer First Name', 'value' => 'order.customer.first_name'],
                ['label' => 'Customer Last Name', 'value' => 'order.customer.last_name'],
                ['label' => 'Customer Orders Count', 'value' => 'order.customer.orders_count'],
                ['label' => 'Customer Total Spent', 'value' => 'order.customer.total_spent'],
                ['label' => 'Customer Tags', 'value' => 'order.customer.tags'],
                ['label' => 'Customer Verified Email', 'value' => 'order.customer.verified_email'],
                ['label' => 'Customer Accepts Marketing', 'value' => 'order.customer.accepts_marketing'],

                // Addresses
                ['label' => 'Billing Name', 'value' => 'order.billing_address.name'],
                ['label' => 'Billing City', 'value' => 'order.billing_address.city'],
                ['label' => 'Billing Country', 'value' => 'order.billing_address.country'],
                ['label' => 'Billing Country Code', 'value' => 'order.billing_address.country_code'],
                ['label' => 'Billing Zip', 'value' => 'order.billing_address.zip'],
                ['label' => 'Billing Province', 'value' => 'order.billing_address.province'],
                
                ['label' => 'Shipping Name', 'value' => 'order.shipping_address.name'],
                ['label' => 'Shipping City', 'value' => 'order.shipping_address.city'],
                ['label' => 'Shipping Country', 'value' => 'order.shipping_address.country'],
                ['label' => 'Shipping Country Code', 'value' => 'order.shipping_address.country_code'],
                ['label' => 'Shipping Zip', 'value' => 'order.shipping_address.zip'],
                ['label' => 'Shipping Province', 'value' => 'order.shipping_address.province'],
                ['label' => 'Shipping Phone', 'value' => 'order.shipping_address.phone'],

                // Fulfillment
                ['label' => 'Fulfillment Status', 'value' => 'order.fulfillment_status'],
                ['label' => 'Shipping Lines Title', 'value' => 'order.shipping_lines.0.title'],
                ['label' => 'Shipping Lines Price', 'value' => 'order.shipping_lines.0.price'],
                
                // Meta
                ['label' => 'Tags', 'value' => 'order.tags'],
                ['label' => 'Note', 'value' => 'order.note'],
                ['label' => 'Referring Site', 'value' => 'order.referring_site'],
                ['label' => 'Landing Site', 'value' => 'order.landing_site'],
                ['label' => 'Cancel Reason', 'value' => 'order.cancel_reason'],
                ['label' => 'Buyer Accepts Marketing', 'value' => 'order.buyer_accepts_marketing'],
                ['label' => 'Test Order', 'value' => 'order.test'],
                ['label' => 'Validation Link', 'value' => 'order.order_status_url'],
            ],
        ],
        [
            'key' => 'orders_paid',
            'label' => 'Order Paid',
            'description' => 'Starts when an order is processed',
            'topic' => 'ORDERS_PAID',
            'category' => 'orders',
            'icon' => 'ShoppingBag',
            'variables' => [
                // Identity
                ['label' => 'Order Name (e.g. #1001)', 'value' => 'order.name'],
                ['label' => 'Order ID', 'value' => 'order.id'],
                ['label' => 'Order Number', 'value' => 'order.order_number'],
                ['label' => 'Processed At', 'value' => 'order.processed_at'],
                
                // Financials
                ['label' => 'Total Price', 'value' => 'order.total_price'],
                ['label' => 'Subtotal', 'value' => 'order.subtotal_price'],
                ['label' => 'Total Tax', 'value' => 'order.total_tax'],
                ['label' => 'Total Discounts', 'value' => 'order.total_discounts'],
                ['label' => 'Total Line Items Price', 'value' => 'order.total_line_items_price'],
                ['label' => 'Outstanding Balance', 'value' => 'order.total_outstanding'],
                ['label' => 'Currency', 'value' => 'order.currency'],
                ['label' => 'Financial Status', 'value' => 'order.financial_status'],
                ['label' => 'Payment Gateway Names', 'value' => 'order.payment_gateway_names.0'], // First gateway
                ['label' => 'Taxes Included', 'value' => 'order.taxes_included'],
                
                // Customer
                ['label' => 'Customer Email', 'value' => 'order.email'],
                ['label' => 'Customer Phone', 'value' => 'order.phone'],
                ['label' => 'Customer ID', 'value' => 'order.customer.id'],
                ['label' => 'Customer First Name', 'value' => 'order.customer.first_name'],
                ['label' => 'Customer Last Name', 'value' => 'order.customer.last_name'],
                ['label' => 'Customer Orders Count', 'value' => 'order.customer.orders_count'],
                ['label' => 'Customer Total Spent', 'value' => 'order.customer.total_spent'],
                ['label' => 'Customer Tags', 'value' => 'order.customer.tags'],
                ['label' => 'Customer Verified Email', 'value' => 'order.customer.verified_email'],
                ['label' => 'Customer Accepts Marketing', 'value' => 'order.customer.accepts_marketing'],

                // Addresses
                ['label' => 'Billing Name', 'value' => 'order.billing_address.name'],
                ['label' => 'Billing City', 'value' => 'order.billing_address.city'],
                ['label' => 'Billing Country', 'value' => 'order.billing_address.country'],
                ['label' => 'Billing Country Code', 'value' => 'order.billing_address.country_code'],
                ['label' => 'Billing Zip', 'value' => 'order.billing_address.zip'],
                ['label' => 'Billing Province', 'value' => 'order.billing_address.province'],
                
                ['label' => 'Shipping Name', 'value' => 'order.shipping_address.name'],
                ['label' => 'Shipping City', 'value' => 'order.shipping_address.city'],
                ['label' => 'Shipping Country', 'value' => 'order.shipping_address.country'],
                ['label' => 'Shipping Country Code', 'value' => 'order.shipping_address.country_code'],
                ['label' => 'Shipping Zip', 'value' => 'order.shipping_address.zip'],
                ['label' => 'Shipping Province', 'value' => 'order.shipping_address.province'],
                ['label' => 'Shipping Phone', 'value' => 'order.shipping_address.phone'],

                // Fulfillment
                ['label' => 'Fulfillment Status', 'value' => 'order.fulfillment_status'],
                ['label' => 'Shipping Lines Title', 'value' => 'order.shipping_lines.0.title'],
                ['label' => 'Shipping Lines Price', 'value' => 'order.shipping_lines.0.price'],
                
                // Meta
                ['label' => 'Tags', 'value' => 'order.tags'],
                ['label' => 'Note', 'value' => 'order.note'],
                ['label' => 'Referring Site', 'value' => 'order.referring_site'],
                ['label' => 'Landing Site', 'value' => 'order.landing_site'],
                ['label' => 'Cancel Reason', 'value' => 'order.cancel_reason'],
                ['label' => 'Buyer Accepts Marketing', 'value' => 'order.buyer_accepts_marketing'],
                ['label' => 'Test Order', 'value' => 'order.test'],
                ['label' => 'Validation Link', 'value' => 'order.order_status_url'],
            ],
        ],
        [
            'key' => 'orders_cancelled',
            'label' => 'Order Canceled',
            'description' => 'Starts when an order is canceled',
            'topic' => 'ORDERS_CANCELLED',
            'category' => 'orders',
            'icon' => 'ShoppingBag',
            'variables' => [
                // Identity
                ['label' => 'Order Name (e.g. #1001)', 'value' => 'order.name'],
                ['label' => 'Order ID', 'value' => 'order.id'],
                ['label' => 'Order Number', 'value' => 'order.order_number'],
                ['label' => 'Processed At', 'value' => 'order.processed_at'],
                
                // Financials
                ['label' => 'Total Price', 'value' => 'order.total_price'],
                ['label' => 'Subtotal', 'value' => 'order.subtotal_price'],
                ['label' => 'Total Tax', 'value' => 'order.total_tax'],
                ['label' => 'Total Discounts', 'value' => 'order.total_discounts'],
                ['label' => 'Total Line Items Price', 'value' => 'order.total_line_items_price'],
                ['label' => 'Outstanding Balance', 'value' => 'order.total_outstanding'],
                ['label' => 'Currency', 'value' => 'order.currency'],
                ['label' => 'Financial Status', 'value' => 'order.financial_status'],
                ['label' => 'Payment Gateway Names', 'value' => 'order.payment_gateway_names.0'], // First gateway
                ['label' => 'Taxes Included', 'value' => 'order.taxes_included'],
                
                // Customer
                ['label' => 'Customer Email', 'value' => 'order.email'],
                ['label' => 'Customer Phone', 'value' => 'order.phone'],
                ['label' => 'Customer ID', 'value' => 'order.customer.id'],
                ['label' => 'Customer First Name', 'value' => 'order.customer.first_name'],
                ['label' => 'Customer Last Name', 'value' => 'order.customer.last_name'],
                ['label' => 'Customer Orders Count', 'value' => 'order.customer.orders_count'],
                ['label' => 'Customer Total Spent', 'value' => 'order.customer.total_spent'],
                ['label' => 'Customer Tags', 'value' => 'order.customer.tags'],
                ['label' => 'Customer Verified Email', 'value' => 'order.customer.verified_email'],
                ['label' => 'Customer Accepts Marketing', 'value' => 'order.customer.accepts_marketing'],

                // Addresses
                ['label' => 'Billing Name', 'value' => 'order.billing_address.name'],
                ['label' => 'Billing City', 'value' => 'order.billing_address.city'],
                ['label' => 'Billing Country', 'value' => 'order.billing_address.country'],
                ['label' => 'Billing Country Code', 'value' => 'order.billing_address.country_code'],
                ['label' => 'Billing Zip', 'value' => 'order.billing_address.zip'],
                ['label' => 'Billing Province', 'value' => 'order.billing_address.province'],
                
                ['label' => 'Shipping Name', 'value' => 'order.shipping_address.name'],
                ['label' => 'Shipping City', 'value' => 'order.shipping_address.city'],
                ['label' => 'Shipping Country', 'value' => 'order.shipping_address.country'],
                ['label' => 'Shipping Country Code', 'value' => 'order.shipping_address.country_code'],
                ['label' => 'Shipping Zip', 'value' => 'order.shipping_address.zip'],
                ['label' => 'Shipping Province', 'value' => 'order.shipping_address.province'],
                ['label' => 'Shipping Phone', 'value' => 'order.shipping_address.phone'],

                // Fulfillment
                ['label' => 'Fulfillment Status', 'value' => 'order.fulfillment_status'],
                ['label' => 'Shipping Lines Title', 'value' => 'order.shipping_lines.0.title'],
                ['label' => 'Shipping Lines Price', 'value' => 'order.shipping_lines.0.price'],
                
                // Meta
                ['label' => 'Tags', 'value' => 'order.tags'],
                ['label' => 'Note', 'value' => 'order.note'],
                ['label' => 'Referring Site', 'value' => 'order.referring_site'],
                ['label' => 'Landing Site', 'value' => 'order.landing_site'],
                ['label' => 'Cancel Reason', 'value' => 'order.cancel_reason'],
                ['label' => 'Buyer Accepts Marketing', 'value' => 'order.buyer_accepts_marketing'],
                ['label' => 'Test Order', 'value' => 'order.test'],
                ['label' => 'Validation Link', 'value' => 'order.order_status_url'],
            ],
        ],
        [
            'key' => 'orders_fulfilled',
            'label' => 'Order Fulfilled',
            'description' => 'Starts when an order is prepared for shipment',
            'topic' => 'ORDERS_FULFILLED',
            'category' => 'orders',
            'icon' => 'ShoppingBag',
            'variables' => [
                // Identity
                ['label' => 'Order Name (e.g. #1001)', 'value' => 'order.name'],
                ['label' => 'Order ID', 'value' => 'order.id'],
                ['label' => 'Order Number', 'value' => 'order.order_number'],
                ['label' => 'Processed At', 'value' => 'order.processed_at'],
                
                // Financials
                ['label' => 'Total Price', 'value' => 'order.total_price'],
                ['label' => 'Subtotal', 'value' => 'order.subtotal_price'],
                ['label' => 'Total Tax', 'value' => 'order.total_tax'],
                ['label' => 'Total Discounts', 'value' => 'order.total_discounts'],
                ['label' => 'Total Line Items Price', 'value' => 'order.total_line_items_price'],
                ['label' => 'Outstanding Balance', 'value' => 'order.total_outstanding'],
                ['label' => 'Currency', 'value' => 'order.currency'],
                ['label' => 'Financial Status', 'value' => 'order.financial_status'],
                ['label' => 'Payment Gateway Names', 'value' => 'order.payment_gateway_names.0'], // First gateway
                ['label' => 'Taxes Included', 'value' => 'order.taxes_included'],
                
                // Customer
                ['label' => 'Customer Email', 'value' => 'order.email'],
                ['label' => 'Customer Phone', 'value' => 'order.phone'],
                ['label' => 'Customer ID', 'value' => 'order.customer.id'],
                ['label' => 'Customer First Name', 'value' => 'order.customer.first_name'],
                ['label' => 'Customer Last Name', 'value' => 'order.customer.last_name'],
                ['label' => 'Customer Orders Count', 'value' => 'order.customer.orders_count'],
                ['label' => 'Customer Total Spent', 'value' => 'order.customer.total_spent'],
                ['label' => 'Customer Tags', 'value' => 'order.customer.tags'],
                ['label' => 'Customer Verified Email', 'value' => 'order.customer.verified_email'],
                ['label' => 'Customer Accepts Marketing', 'value' => 'order.customer.accepts_marketing'],

                // Addresses
                ['label' => 'Billing Name', 'value' => 'order.billing_address.name'],
                ['label' => 'Billing City', 'value' => 'order.billing_address.city'],
                ['label' => 'Billing Country', 'value' => 'order.billing_address.country'],
                ['label' => 'Billing Country Code', 'value' => 'order.billing_address.country_code'],
                ['label' => 'Billing Zip', 'value' => 'order.billing_address.zip'],
                ['label' => 'Billing Province', 'value' => 'order.billing_address.province'],
                
                ['label' => 'Shipping Name', 'value' => 'order.shipping_address.name'],
                ['label' => 'Shipping City', 'value' => 'order.shipping_address.city'],
                ['label' => 'Shipping Country', 'value' => 'order.shipping_address.country'],
                ['label' => 'Shipping Country Code', 'value' => 'order.shipping_address.country_code'],
                ['label' => 'Shipping Zip', 'value' => 'order.shipping_address.zip'],
                ['label' => 'Shipping Province', 'value' => 'order.shipping_address.province'],
                ['label' => 'Shipping Phone', 'value' => 'order.shipping_address.phone'],

                // Fulfillment
                ['label' => 'Fulfillment Status', 'value' => 'order.fulfillment_status'],
                ['label' => 'Shipping Lines Title', 'value' => 'order.shipping_lines.0.title'],
                ['label' => 'Shipping Lines Price', 'value' => 'order.shipping_lines.0.price'],
                
                // Meta
                ['label' => 'Tags', 'value' => 'order.tags'],
                ['label' => 'Note', 'value' => 'order.note'],
                ['label' => 'Referring Site', 'value' => 'order.referring_site'],
                ['label' => 'Landing Site', 'value' => 'order.landing_site'],
                ['label' => 'Cancel Reason', 'value' => 'order.cancel_reason'],
                ['label' => 'Buyer Accepts Marketing', 'value' => 'order.buyer_accepts_marketing'],
                ['label' => 'Test Order', 'value' => 'order.test'],
                ['label' => 'Validation Link', 'value' => 'order.order_status_url'],
            ],
        ],
        [
            'key' => 'orders_partially_fulfilled',
            'label' => 'Order Partially Fulfilled',
            'description' => 'Starts when a new partial order fulfillment is created',
            'topic' => 'ORDERS_PARTIALLY_FULFILLED',
            'category' => 'orders',
            'icon' => 'ShoppingBag',
            'variables' => [
                // Identity
                ['label' => 'Order Name (e.g. #1001)', 'value' => 'order.name'],
                ['label' => 'Order ID', 'value' => 'order.id'],
                ['label' => 'Order Number', 'value' => 'order.order_number'],
                ['label' => 'Processed At', 'value' => 'order.processed_at'],
                
                // Financials
                ['label' => 'Total Price', 'value' => 'order.total_price'],
                ['label' => 'Subtotal', 'value' => 'order.subtotal_price'],
                ['label' => 'Total Tax', 'value' => 'order.total_tax'],
                ['label' => 'Total Discounts', 'value' => 'order.total_discounts'],
                ['label' => 'Total Line Items Price', 'value' => 'order.total_line_items_price'],
                ['label' => 'Outstanding Balance', 'value' => 'order.total_outstanding'],
                ['label' => 'Currency', 'value' => 'order.currency'],
                ['label' => 'Financial Status', 'value' => 'order.financial_status'],
                ['label' => 'Payment Gateway Names', 'value' => 'order.payment_gateway_names.0'], // First gateway
                ['label' => 'Taxes Included', 'value' => 'order.taxes_included'],
                
                // Customer
                ['label' => 'Customer Email', 'value' => 'order.email'],
                ['label' => 'Customer Phone', 'value' => 'order.phone'],
                ['label' => 'Customer ID', 'value' => 'order.customer.id'],
                ['label' => 'Customer First Name', 'value' => 'order.customer.first_name'],
                ['label' => 'Customer Last Name', 'value' => 'order.customer.last_name'],
                ['label' => 'Customer Orders Count', 'value' => 'order.customer.orders_count'],
                ['label' => 'Customer Total Spent', 'value' => 'order.customer.total_spent'],
                ['label' => 'Customer Tags', 'value' => 'order.customer.tags'],
                ['label' => 'Customer Verified Email', 'value' => 'order.customer.verified_email'],
                ['label' => 'Customer Accepts Marketing', 'value' => 'order.customer.accepts_marketing'],

                // Addresses
                ['label' => 'Billing Name', 'value' => 'order.billing_address.name'],
                ['label' => 'Billing City', 'value' => 'order.billing_address.city'],
                ['label' => 'Billing Country', 'value' => 'order.billing_address.country'],
                ['label' => 'Billing Country Code', 'value' => 'order.billing_address.country_code'],
                ['label' => 'Billing Zip', 'value' => 'order.billing_address.zip'],
                ['label' => 'Billing Province', 'value' => 'order.billing_address.province'],
                
                ['label' => 'Shipping Name', 'value' => 'order.shipping_address.name'],
                ['label' => 'Shipping City', 'value' => 'order.shipping_address.city'],
                ['label' => 'Shipping Country', 'value' => 'order.shipping_address.country'],
                ['label' => 'Shipping Country Code', 'value' => 'order.shipping_address.country_code'],
                ['label' => 'Shipping Zip', 'value' => 'order.shipping_address.zip'],
                ['label' => 'Shipping Province', 'value' => 'order.shipping_address.province'],
                ['label' => 'Shipping Phone', 'value' => 'order.shipping_address.phone'],

                // Fulfillment
                ['label' => 'Fulfillment Status', 'value' => 'order.fulfillment_status'],
                ['label' => 'Shipping Lines Title', 'value' => 'order.shipping_lines.0.title'],
                ['label' => 'Shipping Lines Price', 'value' => 'order.shipping_lines.0.price'],
                
                // Meta
                ['label' => 'Tags', 'value' => 'order.tags'],
                ['label' => 'Note', 'value' => 'order.note'],
                ['label' => 'Referring Site', 'value' => 'order.referring_site'],
                ['label' => 'Landing Site', 'value' => 'order.landing_site'],
                ['label' => 'Cancel Reason', 'value' => 'order.cancel_reason'],
                ['label' => 'Buyer Accepts Marketing', 'value' => 'order.buyer_accepts_marketing'],
                ['label' => 'Test Order', 'value' => 'order.test'],
                ['label' => 'Validation Link', 'value' => 'order.order_status_url'],
            ],
        ],
        
        // Products
        [
            'key' => 'products_create',
            'label' => 'Product Created',
            'description' => 'Starts when a product is created',
            'topic' => 'PRODUCTS_CREATE',
            'category' => 'products',
            'icon' => 'Package',
            'variables' => [
                ['label' => 'Product Title', 'value' => 'product.title'],
                ['label' => 'Product ID', 'value' => 'product.id'],
                ['label' => 'Product Handle', 'value' => 'product.handle'],
                ['label' => 'Product Type', 'value' => 'product.product_type'],
                ['label' => 'Vendor', 'value' => 'product.vendor'],
                ['label' => 'Status', 'value' => 'product.status'],
                ['label' => 'Tags', 'value' => 'product.tags'],
                ['label' => 'Template Suffix', 'value' => 'product.template_suffix'],
                ['label' => 'Published Scope', 'value' => 'product.published_scope'],
                ['label' => 'Created At', 'value' => 'product.created_at'],
                ['label' => 'Published At', 'value' => 'product.published_at'],
                // Variants
                ['label' => 'Total Variants', 'value' => 'product.variants.length'],
                ['label' => 'First Variant Price', 'value' => 'product.variants.0.price'],
                ['label' => 'First Variant SKU', 'value' => 'product.variants.0.sku'],
                ['label' => 'First Variant Inventory', 'value' => 'product.variants.0.inventory_quantity'],
                ['label' => 'First Variant Weight', 'value' => 'product.variants.0.weight'],
                ['label' => 'First Variant Requires Shipping', 'value' => 'product.variants.0.requires_shipping'],
                ['label' => 'First Variant Taxable', 'value' => 'product.variants.0.taxable'],
            ],
        ],
        [
            'key' => 'products_update',
            'label' => 'Product Updated',
            'description' => 'Starts when a product is updated',
            'topic' => 'PRODUCTS_UPDATE',
            'category' => 'products',
            'icon' => 'Package',
            'variables' => [
                ['label' => 'Product Title', 'value' => 'product.title'],
                ['label' => 'Product ID', 'value' => 'product.id'],
                ['label' => 'Product Handle', 'value' => 'product.handle'],
                ['label' => 'Product Type', 'value' => 'product.product_type'],
                ['label' => 'Vendor', 'value' => 'product.vendor'],
                ['label' => 'Status', 'value' => 'product.status'],
                ['label' => 'Tags', 'value' => 'product.tags'],
                ['label' => 'Template Suffix', 'value' => 'product.template_suffix'],
                ['label' => 'Published Scope', 'value' => 'product.published_scope'],
                ['label' => 'Created At', 'value' => 'product.created_at'],
                ['label' => 'Published At', 'value' => 'product.published_at'],
                // Variants
                ['label' => 'Total Variants', 'value' => 'product.variants.length'],
                ['label' => 'First Variant Price', 'value' => 'product.variants.0.price'],
                ['label' => 'First Variant SKU', 'value' => 'product.variants.0.sku'],
                ['label' => 'First Variant Inventory', 'value' => 'product.variants.0.inventory_quantity'],
                ['label' => 'First Variant Weight', 'value' => 'product.variants.0.weight'],
                ['label' => 'First Variant Requires Shipping', 'value' => 'product.variants.0.requires_shipping'],
                ['label' => 'First Variant Taxable', 'value' => 'product.variants.0.taxable'],
            ],
        ],
        [
            'key' => 'products_delete',
            'label' => 'Product Deleted',
            'description' => 'Starts when a product is removed',
            'topic' => 'PRODUCTS_DELETE',
            'category' => 'products',
            'icon' => 'Package',
            'variables' => [
                 ['label' => 'Product ID', 'value' => 'product.id'],
            ],
        ],
        
        // Customers
        [
            'key' => 'customers_create',
            'label' => 'Customer Created',
            'description' => 'Starts when a customer is created',
            'topic' => 'CUSTOMERS_CREATE',
            'category' => 'customers',
            'icon' => 'Users',
            'variables' => [
                ['label' => 'First Name', 'value' => 'customer.first_name'],
                ['label' => 'Last Name', 'value' => 'customer.last_name'],
                ['label' => 'Email', 'value' => 'customer.email'],
                ['label' => 'Customer ID', 'value' => 'customer.id'],
                ['label' => 'Phone', 'value' => 'customer.phone'],
                ['label' => 'Total Spent', 'value' => 'customer.total_spent'],
                ['label' => 'Orders Count', 'value' => 'customer.orders_count'],
                ['label' => 'State', 'value' => 'customer.state'],
                ['label' => 'Verified Email', 'value' => 'customer.verified_email'],
                ['label' => 'Accepts Marketing', 'value' => 'customer.accepts_marketing'],
                ['label' => 'Tax Exempt', 'value' => 'customer.tax_exempt'],
                ['label' => 'Currency', 'value' => 'customer.currency'],
                ['label' => 'Tags', 'value' => 'customer.tags'],
                ['label' => 'Created At', 'value' => 'customer.created_at'],
                // Default Address
                ['label' => 'Default Address City', 'value' => 'customer.default_address.city'],
                ['label' => 'Default Address Country', 'value' => 'customer.default_address.country_code'],
                ['label' => 'Default Address State', 'value' => 'customer.default_address.province'],
                ['label' => 'Default Address Zip', 'value' => 'customer.default_address.zip'],
                ['label' => 'Default Address Company', 'value' => 'customer.default_address.company'],
            ],
        ],
        [
            'key' => 'customers_update',
            'label' => 'Customer Updated',
            'description' => 'Starts when a customer is updated',
            'topic' => 'CUSTOMERS_UPDATE',
            'category' => 'customers',
            'icon' => 'Users',
            'variables' => [
                ['label' => 'First Name', 'value' => 'customer.first_name'],
                ['label' => 'Last Name', 'value' => 'customer.last_name'],
                ['label' => 'Email', 'value' => 'customer.email'],
                ['label' => 'Customer ID', 'value' => 'customer.id'],
                ['label' => 'Phone', 'value' => 'customer.phone'],
                ['label' => 'Total Spent', 'value' => 'customer.total_spent'],
                ['label' => 'Orders Count', 'value' => 'customer.orders_count'],
                ['label' => 'State', 'value' => 'customer.state'],
                ['label' => 'Verified Email', 'value' => 'customer.verified_email'],
                ['label' => 'Accepts Marketing', 'value' => 'customer.accepts_marketing'],
                ['label' => 'Tax Exempt', 'value' => 'customer.tax_exempt'],
                ['label' => 'Currency', 'value' => 'customer.currency'],
                ['label' => 'Tags', 'value' => 'customer.tags'],
                ['label' => 'Created At', 'value' => 'customer.created_at'],
                // Default Address
                ['label' => 'Default Address City', 'value' => 'customer.default_address.city'],
                ['label' => 'Default Address Country', 'value' => 'customer.default_address.country_code'],
                ['label' => 'Default Address State', 'value' => 'customer.default_address.province'],
                ['label' => 'Default Address Zip', 'value' => 'customer.default_address.zip'],
                ['label' => 'Default Address Company', 'value' => 'customer.default_address.company'],
            ],
        ],
        [
            'key' => 'customers_delete',
            'label' => 'Customer Deleted',
            'description' => 'Starts when a customer is removed',
            'topic' => 'CUSTOMERS_DELETE',
            'category' => 'customers',
            'icon' => 'Users',
            'variables' => [
                 ['label' => 'Customer ID', 'value' => 'customer.id'],
            ],
        ],
        
        // Collections
        [
            'key' => 'collections_create',
            'label' => 'Collection Created',
            'description' => 'Starts when a collection is created',
            'topic' => 'COLLECTIONS_CREATE',
            'category' => 'collections',
            'icon' => 'Grid',
            'variables' => [
                ['label' => 'Collection Title', 'value' => 'collection.title'],
                ['label' => 'Collection ID', 'value' => 'collection.id'],
                ['label' => 'Handle', 'value' => 'collection.handle'],
                ['label' => 'Updated At', 'value' => 'collection.updated_at'],
                ['label' => 'Published At', 'value' => 'collection.published_at'],
                ['label' => 'Sort Order', 'value' => 'collection.sort_order'],
                ['label' => 'Template Suffix', 'value' => 'collection.template_suffix'],
                ['label' => 'Products Count', 'value' => 'collection.products_count'],
            ],
        ],
        [
            'key' => 'collections_update',
            'label' => 'Collection Updated',
            'description' => 'Starts when a collection is updated',
            'topic' => 'COLLECTIONS_UPDATE',
            'category' => 'collections',
            'icon' => 'Grid',
            'variables' => [
                ['label' => 'Collection Title', 'value' => 'collection.title'],
                ['label' => 'Collection ID', 'value' => 'collection.id'],
                ['label' => 'Handle', 'value' => 'collection.handle'],
                ['label' => 'Updated At', 'value' => 'collection.updated_at'],
            ],
        ],
        [
            'key' => 'collections_delete',
            'label' => 'Collection Deleted',
            'description' => 'Starts when a collection is removed',
            'topic' => 'COLLECTIONS_DELETE',
            'category' => 'collections',
            'icon' => 'Grid',
            'variables' => [
                 ['label' => 'Collection ID', 'value' => 'collection.id'],
            ],
        ],
        
        // Fulfillments
        [
            'key' => 'fulfillments_create',
            'label' => 'Fulfillment Created',
            'description' => 'Starts when a fulfillment is created',
            'topic' => 'FULFILLMENTS_CREATE',
            'category' => 'fulfillments',
            'icon' => 'Truck',
            'variables' => [
                ['label' => 'Fulfillment ID', 'value' => 'fulfillment.id'],
                ['label' => 'Order ID', 'value' => 'fulfillment.order_id'],
                ['label' => 'Status', 'value' => 'fulfillment.status'],
                ['label' => 'Tracking Company', 'value' => 'fulfillment.tracking_company'],
                ['label' => 'Tracking Number', 'value' => 'fulfillment.tracking_number'],
                ['label' => 'Tracking URL', 'value' => 'fulfillment.tracking_url'],
                ['label' => 'Created At', 'value' => 'fulfillment.created_at'],
                ['label' => 'Service', 'value' => 'fulfillment.service'],
                ['label' => 'Shipment Status', 'value' => 'fulfillment.shipment_status'],
                ['label' => 'Location ID', 'value' => 'fulfillment.location_id'],
            ],
        ],
        [
            'key' => 'fulfillments_update',
            'label' => 'Fulfillment Updated',
            'description' => 'Starts when a fulfillment is updated',
            'topic' => 'FULFILLMENTS_UPDATE',
            'category' => 'fulfillments',
            'icon' => 'Truck',
             'variables' => [
                ['label' => 'Fulfillment ID', 'value' => 'fulfillment.id'],
                ['label' => 'Status', 'value' => 'fulfillment.status'],
                ['label' => 'Tracking Number', 'value' => 'fulfillment.tracking_number'],
                ['label' => 'Shipment Status', 'value' => 'fulfillment.shipment_status'],
                ['label' => 'Updated At', 'value' => 'fulfillment.updated_at'],
            ],
        ],

        // Draft Orders
        [
            'key' => 'draft_orders_create',
            'label' => 'Draft Order Created',
            'description' => 'Starts when a draft order is created',
            'topic' => 'DRAFT_ORDERS_CREATE',
            'category' => 'draft_orders',
            'icon' => 'FileText',
            'variables' => [
                ['label' => 'Draft Order Name', 'value' => 'draft_order.name'],
                ['label' => 'Draft Order ID', 'value' => 'draft_order.id'],
                ['label' => 'Total Price', 'value' => 'draft_order.total_price'],
                ['label' => 'Subtotal', 'value' => 'draft_order.subtotal_price'],
                ['label' => 'Status', 'value' => 'draft_order.status'],
                ['label' => 'Currency', 'value' => 'draft_order.currency'],
                ['label' => 'Note', 'value' => 'draft_order.note'],
                ['label' => 'Email', 'value' => 'draft_order.email'],
                ['label' => 'Tax Exempt', 'value' => 'draft_order.tax_exempt'],
                ['label' => 'Customer ID', 'value' => 'draft_order.customer.id'],
            ],
        ],
        [
            'key' => 'draft_orders_updated',
            'label' => 'Draft Order Updated',
            'description' => 'Starts when a draft order is updated',
            'topic' => 'DRAFT_ORDERS_UPDATED',
            'category' => 'draft_orders',
            'icon' => 'FileText',
            'variables' => [
                ['label' => 'Draft Order Name', 'value' => 'draft_order.name'],
                ['label' => 'Total Price', 'value' => 'draft_order.total_price'],
                ['label' => 'Status', 'value' => 'draft_order.status'],
                ['label' => 'Updated At', 'value' => 'draft_order.updated_at'],
            ],
        ],

        // Refunds
        [
            'key' => 'refunds_create',
            'label' => 'Refund Created',
            'description' => 'Starts when a refund is created',
            'topic' => 'REFUNDS_CREATE',
            'category' => 'refunds',
            'icon' => 'ArrowBack',
            'variables' => [
                ['label' => 'Refund ID', 'value' => 'refund.id'],
                ['label' => 'Order ID', 'value' => 'refund.order_id'],
                ['label' => 'Created At', 'value' => 'refund.created_at'],
                ['label' => 'Note', 'value' => 'refund.note'],
                ['label' => 'Restock', 'value' => 'refund.restock'],
                ['label' => 'User ID', 'value' => 'refund.user_id'],
                ['label' => 'Processed At', 'value' => 'refund.processed_at'],
            ],
        ],

        // Shop
        [
            'key' => 'shop_update',
            'label' => 'Shop Updated',
            'description' => 'Starts when a shop is updated',
            'topic' => 'SHOP_UPDATE',
            'category' => 'shop',
            'icon' => 'Store',
            'variables' => [
                ['label' => 'Shop Name', 'value' => 'shop.name'],
                ['label' => 'Shop ID', 'value' => 'shop.id'],
                ['label' => 'Email', 'value' => 'shop.email'],
                ['label' => 'Domain', 'value' => 'shop.domain'],
                ['label' => 'Country', 'value' => 'shop.country'],
                ['label' => 'Currency', 'value' => 'shop.currency'],
                ['label' => 'Timezone', 'value' => 'shop.timezone'],
                ['label' => 'Plan Name', 'value' => 'shop.plan_name'],
                ['label' => 'Phone', 'value' => 'shop.phone'],
                ['label' => 'Customer Email', 'value' => 'shop.customer_email'],
            ],
        ],

        // Discounts
        [
            'key' => 'discounts_create',
            'label' => 'Discount Created',
            'description' => 'Starts when a discount is created',
            'topic' => 'DISCOUNTS_CREATE',
            'category' => 'discounts',
            'icon' => 'Tag',
            'variables' => [
                ['label' => 'Discount Code', 'value' => 'discount.code'],
                ['label' => 'Discount ID', 'value' => 'discount.id'],
                ['label' => 'Value', 'value' => 'discount.value'],
                ['label' => 'Value Type', 'value' => 'discount.value_type'],
                ['label' => 'Usage Count', 'value' => 'discount.usage_count'],
                ['label' => 'Starts At', 'value' => 'discount.starts_at'],
                ['label' => 'Ends At', 'value' => 'discount.ends_at'],
                ['label' => 'Minimum Order Amount', 'value' => 'discount.minimum_order_amount'],
            ],
        ],
        [
            'key' => 'discounts_update',
            'label' => 'Discount Updated',
            'description' => 'Starts when a discount is updated',
            'topic' => 'DISCOUNTS_UPDATE',
            'category' => 'discounts',
            'icon' => 'Tag',
            'variables' => [
                ['label' => 'Discount Code', 'value' => 'discount.code'],
                ['label' => 'Value', 'value' => 'discount.value'],
                ['label' => 'Updated At', 'value' => 'discount.updated_at'],
            ],
        ],

        // Inventory
        [
            'key' => 'inventory_levels_update',
            'label' => 'Inventory Updated',
            'description' => 'Starts when an inventory level is updated',
            'topic' => 'INVENTORY_LEVELS_UPDATE',
            'category' => 'inventory',
            'icon' => 'Database',
            'variables' => [
                ['label' => 'Inventory Item ID', 'value' => 'inventory_level.inventory_item_id'],
                ['label' => 'Location ID', 'value' => 'inventory_level.location_id'],
                ['label' => 'Available', 'value' => 'inventory_level.available'],
                ['label' => 'Updated At', 'value' => 'inventory_level.updated_at'],
            ],
        ],

        // Checkouts
        [
            'key' => 'checkouts_create',
            'label' => 'Checkout Created',
            'description' => 'Starts when a checkout is created',
            'topic' => 'CHECKOUTS_CREATE',
            'category' => 'checkouts',
            'icon' => 'CreditCard',
            'variables' => [
                ['label' => 'Checkout ID', 'value' => 'checkout.id'],
                ['label' => 'Token', 'value' => 'checkout.token'],
                ['label' => 'Cart Token', 'value' => 'checkout.cart_token'],
                ['label' => 'Email', 'value' => 'checkout.email'],
                ['label' => 'Total Price', 'value' => 'checkout.total_price'],
                ['label' => 'Subtotal', 'value' => 'checkout.subtotal_price'],
                ['label' => 'Total Tax', 'value' => 'checkout.total_tax'],
                ['label' => 'Currency', 'value' => 'checkout.currency'],
                ['label' => 'Created At', 'value' => 'checkout.created_at'],
                ['label' => 'Completed At', 'value' => 'checkout.completed_at'],
                ['label' => 'Shipping Address City', 'value' => 'checkout.shipping_address.city'],
                ['label' => 'Billing Address City', 'value' => 'checkout.billing_address.city'],
            ],
        ],
        [
            'key' => 'checkouts_update',
            'label' => 'Checkout Updated',
            'description' => 'Starts when a checkout is updated',
            'topic' => 'CHECKOUTS_UPDATE',
            'category' => 'checkouts',
            'icon' => 'CreditCard',
            'variables' => [
                ['label' => 'Checkout ID', 'value' => 'checkout.id'],
                ['label' => 'Total Price', 'value' => 'checkout.total_price'],
                ['label' => 'Email', 'value' => 'checkout.email'],
                ['label' => 'Updated At', 'value' => 'checkout.updated_at'],
            ],
        ],

        // Carts
        [
            'key' => 'carts_create',
            'label' => 'Cart Created',
            'description' => 'Starts when a cart is created',
            'topic' => 'CARTS_CREATE',
            'category' => 'carts',
            'icon' => 'ShoppingCart',
            'variables' => [
                ['label' => 'Cart ID', 'value' => 'cart.id'],
                ['label' => 'Cart Token', 'value' => 'cart.token'],
                ['label' => 'Note', 'value' => 'cart.note'],
                ['label' => 'Updated At', 'value' => 'cart.updated_at'],
                ['label' => 'Created At', 'value' => 'cart.created_at'],
                ['label' => 'Line Items Count', 'value' => 'cart.line_items.length'],
            ],
        ],
        [
            'key' => 'carts_update',
            'label' => 'Cart Updated',
            'description' => 'Starts when a cart is updated',
            'topic' => 'CARTS_UPDATE',
            'category' => 'carts',
            'icon' => 'ShoppingCart',
            'variables' => [
                ['label' => 'Cart ID', 'value' => 'cart.id'],
                ['label' => 'Note', 'value' => 'cart.note'],
                ['label' => 'Updated At', 'value' => 'cart.updated_at'],
            ],
        ],

        // App
        [
            'key' => 'app_subscriptions_update',
            'label' => 'App Subscription Updated',
            'description' => 'Starts when an app subscription is updated',
            'topic' => 'APP_SUBSCRIPTIONS_UPDATE',
            'category' => 'app',
            'icon' => 'AppWindow',
             'variables' => [
                ['label' => 'Subscription ID', 'value' => 'app_subscription.id'],
                ['label' => 'Name', 'value' => 'app_subscription.name'],
                ['label' => 'Status', 'value' => 'app_subscription.status'],
                ['label' => 'Test', 'value' => 'app_subscription.test'],
                ['label' => 'Trial Days', 'value' => 'app_subscription.trial_days'],
                ['label' => 'Price', 'value' => 'app_subscription.price'],
            ],
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
            'description' => 'Trigger an external webhook',
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
                 ['name' => 'spreadsheet_id', 'label' => 'Spreadsheet', 'type' => 'resource_select', 'resource' => 'google_sheets', 'required' => true, 'placeholder' => 'Select a sheet...'],
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
                 ['name' => 'folder_id', 'label' => 'Parent Folder', 'type' => 'resource_select', 'resource' => 'drive_folders', 'placeholder' => 'Select folder (optional)...'],
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
                 ['name' => 'folder_id', 'label' => 'Parent Folder', 'type' => 'resource_select', 'resource' => 'drive_folders', 'placeholder' => 'Select folder (optional)...'],
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
                    ['value' => 'shop_email', 'label' => 'My Shop Email (Admin)'],
                    ['value' => 'customer_email', 'label' => 'Customer Email (from Trigger)'],
                    ['value' => 'order_email', 'label' => 'Order Email (from Trigger)'],
                 ], 'default' => 'custom'],
                 ['name' => 'to', 'label' => 'To (Custom)', 'type' => 'text', 'placeholder' => 'recipient@example.com', 'showIf' => ['field' => 'recipient_type', 'value' => 'custom']],
                 ['name' => 'subject', 'label' => 'Subject', 'type' => 'text', 'required' => true, 'placeholder' => 'Update on {{ product.title }}'],
                 ['name' => 'body', 'label' => 'Body (HTML)', 'type' => 'textarea', 'required' => true, 'placeholder' => '<h1>New Order: {{ order.name }}</h1><p>Total: {{ order.total_price }}</p>'],
            ]
        ],
        // Slack Actions
        [
            'key' => 'send_slack_message',
            'label' => 'Send Slack Message',
            'description' => 'Send a message to a Slack channel',
            'category' => 'communication',
            'icon' => 'MessageSquare',
            'app' => 'slack',
            'fields' => [
                 ['name' => 'channel', 'label' => 'Channel', 'type' => 'resource_select', 'resource' => 'channels', 'required' => true, 'placeholder' => 'Select a channel...'],
                 ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Hello {{ order.name }}'],
            ]
        ],
        // Klaviyo Actions
        [
            'key' => 'add_profile_to_klaviyo',
            'label' => 'Add Profile to Klaviyo',
            'description' => 'Create or update a profile in Klaviyo',
            'category' => 'marketing',
            'icon' => 'UserPlus',
            'app' => 'klaviyo',
            'fields' => [
                 ['name' => 'email', 'label' => 'Email', 'type' => 'text', 'required' => true, 'placeholder' => '{{ customer.email }}'],
                 ['name' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'placeholder' => '{{ customer.first_name }}'],
                 ['name' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'placeholder' => '{{ customer.last_name }}'],
                 ['name' => 'phone_number', 'label' => 'Phone Number', 'type' => 'text', 'placeholder' => '{{ customer.phone }}'],
            ],
        ],
        [
            'key' => 'add_to_klaviyo_list',
            'label' => 'Add to Klaviyo List',
            'description' => 'Subscribe a profile to a specific Klaviyo list',
            'category' => 'marketing',
            'icon' => 'List',
            'app' => 'klaviyo',
            'fields' => [
                 ['name' => 'list_id', 'label' => 'List', 'type' => 'resource_select', 'resource' => 'lists', 'required' => true, 'placeholder' => 'Select a list...'],
                 ['name' => 'email', 'label' => 'Email', 'type' => 'text', 'required' => true, 'placeholder' => '{{ customer.email }}'],
            ]
        ],
        [
            'key' => 'track_klaviyo_event',
            'label' => 'Track Klaviyo Event',
            'description' => 'Track a custom event (metric) in Klaviyo',
            'category' => 'marketing',
            'icon' => 'Activity',
            'app' => 'klaviyo',
            'fields' => [
                 ['name' => 'event_name', 'label' => 'Event Name', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. Order Delivered'],
                 ['name' => 'email', 'label' => 'Customer Email', 'type' => 'text', 'required' => true, 'placeholder' => '{{ customer.email }}'],
                 ['name' => 'properties', 'label' => 'Event Properties (JSON)', 'type' => 'textarea', 'placeholder' => '{"OrderId": "{{ order.id }}", "Status": "Delivered"}'],
                 ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'placeholder' => '100.00'],
            ]
        ],
    ],
];
