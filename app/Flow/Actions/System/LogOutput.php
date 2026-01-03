<?php

namespace App\Flow\Actions\System;

use App\Flow\Actions\BaseAction;
use App\Models\Node;
use App\Models\Execution;
use Illuminate\Support\Facades\Log;

class LogOutput extends BaseAction
{
    public function handle(Node $node, array $payload, Execution $execution): void
    {
        $settings = $this->getSettings($node);
        $message = $settings['message'] ?? 'No message provided.';
        
        Log::info("Flow Engine Log: " . $message, [
            'flow_id' => $execution->flow_id,
            'node_id' => $node->id
        ]);

        $this->log($execution, $node->id, 'info', "Output Log: " . $message);
    }
}
