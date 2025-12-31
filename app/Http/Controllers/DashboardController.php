<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $shop = Auth::user();

        // Fetch recent executions from DB
        $recentExecutions = DB::table('webhook_logs')
            ->where('shop_id', $shop->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Calculate stats
        $stats = [
            'total_executions' => DB::table('webhook_logs')->where('shop_id', $shop->id)->count(),
            'failed_executions' => DB::table('webhook_logs')->where('shop_id', $shop->id)->where('status', 'failed')->count(),
            'success_executions' => DB::table('webhook_logs')->where('shop_id', $shop->id)->where('status', 'processed')->count(),
        ];

        // N8N URL for iframe
        // Assuming we pass the shop ID or a specific project ID to load the correct context
        $n8nUrl = config('services.n8n.public_url', 'https://primary-production-d49a.up.railway.app'); 

        return Inertia::render('Dashboard', [
            'shop' => $shop,
            'stats' => $stats,
            'executions' => $recentExecutions,
            'n8nUrl' => $n8nUrl,
        ]);
    }
}
