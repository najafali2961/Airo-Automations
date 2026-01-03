<?php

namespace App\Flow\Contracts;

use App\Models\Node;
use App\Models\Execution;

interface ActionInterface
{
    /**
     * Handle the execution of the action.
     * 
     * @param Node $node The current flow node
     * @param array $payload The webhook payload
     * @param Execution $execution The current execution record
     * @return void
     */
    public function handle(Node $node, array $payload, Execution $execution): void;
}
