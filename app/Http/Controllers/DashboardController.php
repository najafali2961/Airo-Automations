<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Execution;
use App\Models\Flow;
use Osiset\ShopifyApp\Messaging\Jobs\WebhookInstaller;
use App\Models\User;
use Osiset\ShopifyApp\Objects\Values\ShopId;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $shop = Auth::user();
        $shopId = $shop->id; // Assuming user->id is shop_id based on previous code

        // Stats
        $stats = [
            'total_executions' => Execution::whereHas('flow', fn($q) => $q->where('shop_id', $shopId))->count(),
            'failed_executions' => Execution::whereHas('flow', fn($q) => $q->where('shop_id', $shopId))->where('status', 'failed')->count(),
            'active_flows' => Flow::where('shop_id', $shopId)->where('active', true)->count(),
            'total_flows' => Flow::where('shop_id', $shopId)->count(),
        ];

        // Recent Executions
        $recentExecutions = Execution::with('flow')
            ->whereHas('flow', fn($q) => $q->where('shop_id', $shopId))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        // Recent Flows
        $recentFlows = Flow::where('shop_id', $shopId)
            ->withCount('executions')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'shop' => $shop,
            'stats' => $stats,
            'executions' => $recentExecutions,
            'flows' => $recentFlows
        ]);
    }

    public function handleShopifyCall()
    {
        $user = User::where('id',1)->first();
        // $query = <<<'GQL'
        // query {
        //   webhookSubscriptions(first: 20) {
        //     edges {
        //       node {
        //         id
        //         topic
        //         endpoint {
        //           __typename
        //           ... on WebhookHttpEndpoint {
        //             callbackUrl
        //           }
        //         }
        //       }
        //     }
        //   } 
        // }
        // GQL;
        // $query = <<<'GQL'
        // query {
        //   webhookSubscriptionsCount(query: "") {
        //     count
        //     precision
        //   } 
        // }
        // GQL;
        // $response = $this->user->api()->graph($query);
        // dd($response);
        // $this->user = User::where('id',1)->first();
        $this->installWebhooks();
    }

    public function installWebhooks()
    {
        $user = User::where('id',1)->first();
        $shopId = ShopId::fromNative($user->id);
        $webhooks = config('shopify-app.webhooks');
        // dd($webhooks);
        info("Webhooks: " . json_encode($webhooks, JSON_PRETTY_PRINT));
        WebhookInstaller::dispatch($shopId, $webhooks);
        dd("Webhooks installed");
    }
}
