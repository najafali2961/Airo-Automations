<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Flow\Engine\ActionRegistry;
use App\Flow\Actions\Shopify\ShopifyGqlAction;
use App\Flow\Actions\System\SendWebhook;
use App\Flow\Actions\Orders\RemoveOrderTag;

class FlowActionTest extends TestCase
{
    public function test_actions_are_registered_correctly()
    {
        $actions = [
            'archive_order' => ShopifyGqlAction::class,
            'unarchive_order' => ShopifyGqlAction::class,
            'send_webhook' => SendWebhook::class,
            'remove_order_tag' => RemoveOrderTag::class,
            'delete_product' => ShopifyGqlAction::class,
            'cancel_order' => ShopifyGqlAction::class,
            'capture_payment' => ShopifyGqlAction::class,
            'hold_fulfillment' => ShopifyGqlAction::class,
        ];

        foreach ($actions as $key => $expectedClass) {
            $action = ActionRegistry::getAction($key);
            $this->assertNotNull($action, "Action for {$key} should not be null");
            $this->assertInstanceOf($expectedClass, $action, "Action for {$key} should be an instance of " . basename($expectedClass));
        }
    }

    public function test_config_contains_new_actions()
    {
        $actions = config('flow.actions');
        $keys = array_column($actions, 'key');

        $this->assertContains('archive_order', $keys);
        $this->assertContains('send_webhook', $keys);
        $this->assertContains('remove_order_tag', $keys);
        $this->assertContains('hold_fulfillment', $keys);
        $this->assertContains('custom_code', $keys);
    }
}
