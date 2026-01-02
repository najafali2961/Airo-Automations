<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Flow;
use App\Models\Execution;
use App\Services\FlowExecutor;
use Illuminate\Support\Facades\Log;

class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $topic;
    public $data;
    
    public function __construct($topic, $data)
    {
        $this->topic = $topic;
        $this->data = $data;
    }
    
    public function handle(FlowExecutor $executor)
    {
        // Find matching flows
        // We look for Trigger nodes with event matching the topic
        $flows = Flow::active()
            ->whereHas('nodes', function ($query) {
                $query->where('type', 'trigger')
                      ->where('settings->event', $this->topic);
            })
            ->get();
            
        if ($flows->isEmpty()) {
            return;
        }

        foreach ($flows as $flow) {
            $eventId = $this->data['id'] ?? null;
            if ($eventId) {
                // Idempotency check
                $exists = Execution::where('flow_id', $flow->id)
                    ->where('external_event_id', (string)$eventId)
                    ->exists();
                if ($exists) continue;
            } else {
                $eventId = uniqid('evt_');
            }
            
            $execution = Execution::create([
                'flow_id' => $flow->id,
                'event' => $this->topic,
                'external_event_id' => (string)$eventId,
                'payload' => $this->data,
                'status' => 'running'
            ]);
            
            try {
                $executor->executeFlow($flow, $this->data, $execution);
            } catch (\Exception $e) {
                $execution->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
                Log::error("Flow Execution Failed: " . $e->getMessage());
            }
        }
    }
}
