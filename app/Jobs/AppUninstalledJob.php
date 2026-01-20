<?php

namespace App\Jobs;

use stdClass;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Osiset\ShopifyApp\Actions\CancelCurrentPlan;
use Osiset\ShopifyApp\Objects\Values\ShopDomain;
use Osiset\ShopifyApp\Contracts\Queries\Shop as IShopQuery;
use Osiset\ShopifyApp\Contracts\Commands\Shop as IShopCommand;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AppUninstalledJob implements ShouldQueue
// class AppUninstalledJob extends \Osiset\ShopifyApp\Messaging\Jobs\AppUninstalledJob
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
     * @param IShopCommand      $shopCommand             The commands for shops.
     * @param IShopQuery        $shopQuery               The querier for shops.
     * @param CancelCurrentPlan $cancelCurrentPlanAction The action for cancelling the current plan.
     *
     * @return bool
     */
    public function handle(
        IShopCommand $shopCommand,
        IShopQuery $shopQuery,
        CancelCurrentPlan $cancelCurrentPlanAction
    ): bool {
        $this->shopDomain = ShopDomain::fromNative($this->shopDomain);
        $shop = User::where('name', $this->shopDomain->toNative())->first();
        if ($shop) {
            // 1. Delete all Flows (Cascades to Nodes, Edges, Executions via DB foreign keys)
            // Note: Flows table does not have a constrained foreign key to 'users', so we delete manually.
            $shop->flows()->delete();

            // 2. Delete Webhook Logs (Manual cleanup)
            if (Schema::hasTable('webhook_logs')) {
                DB::table('webhook_logs')->where('shop_id', $shop->id)->delete();
            }

            // 3. Conditional Cleanup for potential other tables (Defensive)
            // Removed undefined tables based on user feedback

            // 4. Delete User Connectors (Since we SoftDelete the User, we must manually clean these up)
            if (method_exists($shop, 'connectors')) {
                $shop->connectors()->delete();
            }

            // Note: We do NOT delete the $shop (User) here manually.
            // We let the framework handling below (softDelete) take care of it.
            // This prevents the "Call to a member function getId() on null" error.
        }
        
        $shop = $shopQuery->getByDomain($this->shopDomain);
        // Ensure shop exists before proceeding (defensive)
        if (!$shop) {
             return true; // Already deleted or not found
        }
        
        $shopId = $shop->getId();
        $cancelCurrentPlanAction($shopId);
        $shopCommand->clean($shopId);
        $shopCommand->softDelete($shopId);

        return true;
    }
}