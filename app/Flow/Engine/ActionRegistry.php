<?php

namespace App\Flow\Engine;

use App\Flow\Contracts\ActionInterface;

class ActionRegistry
{
    protected static $actions = [
        // System
        'log_output' => \App\Flow\Actions\System\LogOutput::class,
        'http_request' => \App\Flow\Actions\System\SendHttpRequest::class,
        'send_webhook' => \App\Flow\Actions\System\SendWebhook::class,
        
        // Generic (context-aware)
        'add_tag' => \App\Flow\Actions\Generic\AddTag::class,
        
        // Orders
        'add_order_tag' => \App\Flow\Actions\Orders\AddOrderTag::class,
        'remove_order_tag' => \App\Flow\Actions\Orders\RemoveOrderTag::class,
        'cancel_order' => \App\Flow\Actions\Shopify\ShopifyGqlAction::class,
        'archive_order' => \App\Flow\Actions\Shopify\ShopifyGqlAction::class,
        'unarchive_order' => \App\Flow\Actions\Shopify\ShopifyGqlAction::class,
        'hold_fulfillment' => \App\Flow\Actions\Shopify\ShopifyGqlAction::class,
        'capture_payment' => \App\Flow\Actions\Shopify\ShopifyGqlAction::class,
        
        // Products
        'add_product_tag' => \App\Flow\Actions\Products\AddProductTag::class,
        'remove_product_tag' => \App\Flow\Actions\Products\RemoveProductTag::class,
        'update_product_status' => \App\Flow\Actions\Products\UpdateProductStatus::class,
        'delete_product' => \App\Flow\Actions\Shopify\ShopifyGqlAction::class,
        
        // Customers
        'add_customer_tag' => \App\Flow\Actions\Customers\AddCustomerTag::class,
        'remove_customer_tag' => \App\Flow\Actions\Customers\RemoveCustomerTag::class,
        'enable_customer' => \App\Flow\Actions\Shopify\ShopifyGqlAction::class,
        'disable_customer' => \App\Flow\Actions\Shopify\ShopifyGqlAction::class,

        // Collections
        'add_to_collection' => \App\Flow\Actions\Shopify\ShopifyGqlAction::class,

        // Google
        'send_gmail' => \App\Flow\Actions\Google\SendEmailAction::class,
        'add_to_sheet' => \App\Flow\Actions\Google\AddToSheetAction::class,
        'create_doc' => \App\Flow\Actions\Google\CreateDocAction::class,
        'create_sheet' => \App\Flow\Actions\Google\CreateSheetAction::class,
        'send_smart_email' => \App\Flow\Actions\Google\SendTriggerContextEmailAction::class,

        // Google Drive
        'create_folder' => \App\Flow\Actions\Google\CreateFolderAction::class,
        'upload_file' => \App\Flow\Actions\Google\UploadFileAction::class,
        'create_text_file' => \App\Flow\Actions\Google\CreateTextFileAction::class,
    ];


    public static function getAction(string $key): ?ActionInterface
    {
        $class = self::$actions[$key] ?? null;

        if ($class && class_exists($class)) {
            return app($class);
        }

        return null;
    }

    public static function register(string $key, string $class)
    {
        self::$actions[$key] = $class;
    }
}
