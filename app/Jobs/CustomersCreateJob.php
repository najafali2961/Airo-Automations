<?php namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Osiset\ShopifyApp\Objects\Values\ShopDomain;
use Illuminate\Support\Facades\Log;
use App\Models\Flow;
use App\Jobs\RunFlowJob;
use stdClass;

class CustomersCreateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Shop's myshopify domain
     *
     * @var ShopDomain|string
     */
    public $shopDomain;

    /**
     * The webhook data
     *
     * @var object
     */
    public $data;

    /**
     * Create a new job instance.
     *
     * @param string   $shopDomain The shop's myshopify domain.
     * @param stdClass $data       The webhook data (JSON decoded).
     *
     * @return void
     */
    public function __construct($shopDomain, $data)
    {
        $this->shopDomain = $shopDomain;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Convert domain
        $this->shopDomain = ShopDomain::fromNative($this->shopDomain);
        $topic = 'customers/create';
        $domain = $this->shopDomain->toNative();

        Log::info("Webhook received: {$topic} for {$domain}");

        // Find shop by domain
        $userModel = config('auth.providers.users.model');
        $shop = $userModel::where('name', $domain)->first();

        if (!$shop) {
            Log::warning("Shop not found for webhook: {$domain}");
            return;
        }

        // Find active flows with matching trigger
        $flows = Flow::where('shop_id', $shop->id)
            ->where('active', true)
            ->whereHas('nodes', function ($query) use ($topic) {
                $query->where('type', 'trigger')
                      ->where('settings->topic', $topic);
            })
            ->get();

        if ($flows->isEmpty()) {
            Log::info("No active flows found for topic: {$topic}");
            return;
        }

        // Dispatch flow execution jobs
        foreach ($flows as $flow) {
            RunFlowJob::dispatch($flow, $this->data, $topic, uniqid('webhook_'));
            Log::info("Dispatched Flow {$flow->id} for {$topic}");
        }
    }
}
