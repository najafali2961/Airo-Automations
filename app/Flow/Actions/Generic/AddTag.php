<?php

namespace App\Flow\Actions\Generic;

use App\Flow\Actions\BaseAction;
use App\Models\Node;
use App\Models\Execution;

/**
 * Generic AddTag action that determines the resource type from context.
 * This provides backward compatibility and convenience.
 */
class AddTag extends BaseAction
{
    public function handle(Node $node, array $payload, Execution $execution): void
    {
        $topic = $execution->event;
        
        // Determine resource type from topic
        if (str_contains($topic, 'PRODUCT')) {
            $action = new \App\Flow\Actions\Products\AddProductTag();
        } elseif (str_contains($topic, 'ORDER')) {
            $action = new \App\Flow\Actions\Orders\AddOrderTag();
        } elseif (str_contains($topic, 'CUSTOMER')) {
            $action = new \App\Flow\Actions\Customers\AddCustomerTag();
        } else {
            $this->log($execution, $node->id, 'error', "Cannot determine resource type from topic: {$topic}");
            return;
        }
        
        $this->log($execution, $node->id, 'info', "Generic add_tag resolved to: " . get_class($action));
        $action->handle($node, $payload, $execution);
    }
}
