<?php

namespace App\Flow\Actions;

use App\Flow\Contracts\ActionInterface;
use App\Models\Node;
use App\Models\Execution;
use Illuminate\Support\Facades\Log;

abstract class BaseAction implements ActionInterface
{
    /**
     * Helper to log messages back to the execution trace.
     */
    protected function log(Execution $execution, ?int $nodeId, string $level, string $message, ?array $data = null): void
    {
        try {
            $execution->logs()->create([
                'node_id' => $nodeId,
                'level' => $level,
                'message' => $message,
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to create ExecutionLog in BaseAction: " . $e->getMessage());
        }
    }

    /**
     * Get the Shopify API instance for the shop owning the execution.
     */
    protected function getShop(Execution $execution)
    {
        $flow = $execution->flow;
        $userModel = config('auth.providers.users.model');
        $shop = $userModel::find($flow->shop_id);

        if (!$shop) {
            throw new \Exception("Shop not found for flow execution context.");
        }

        return $shop;
    }
}
