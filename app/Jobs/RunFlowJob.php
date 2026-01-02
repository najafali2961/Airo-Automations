<?php

namespace App\Jobs;

use App\Models\Flow;
use App\Services\FlowEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunFlowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $flow;
    protected $payload;
    protected $topic;
    protected $externalEventId;

    /**
     * Create a new job instance.
     */
    public function __construct(Flow $flow, array $payload, string $topic, string $externalEventId)
    {
        $this->flow = $flow;
        $this->payload = $payload;
        $this->topic = $topic;
        $this->externalEventId = $externalEventId;
    }

    /**
     * Execute the job.
     */
    public function handle(FlowEngine $engine): void
    {
        $engine->run($this->flow, $this->payload, $this->topic, $this->externalEventId);
    }
}
