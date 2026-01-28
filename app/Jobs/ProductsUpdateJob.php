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

class ProductsUpdateJob implements ShouldQueue
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
        $topic = 'products/update';
        $normalizedTopic = 'PRODUCTS_UPDATE';
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
            ->whereHas('nodes', function ($query) use ($topic, $normalizedTopic) {
                $query->where('type', 'trigger')
                      ->where(function ($q) use ($topic, $normalizedTopic) {
                           $q->where('settings->topic', $topic)
                             ->orWhere('settings->topic', $normalizedTopic);
                      });
            })
            ->get();

        if ($flows->isEmpty()) {
            Log::info("No active flows found for topic: {$topic} or {$normalizedTopic}");
            return;
        }

        // Dispatch RunFlowJob for each matching flow
        foreach ($flows as $flow) {
            RunFlowJob::dispatch($flow, (array)$this->data, $topic, (string)($this->data->id ?? uniqid()));
            Log::info("Dispatched Flow ID: {$flow->id} for topic: {$topic}");
        }
    }
}
