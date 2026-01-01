<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $n8nService;

    public function __construct(\App\Services\N8NService $n8nService)
    {
        $this->n8nService = $n8nService;
    }

    public function index(Request $request)
    {
        $shop = Auth::user();

        // Fetch recent executions from DB (Webhooks received)
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

        // N8N URL
        $n8nUrl = config('services.n8n.public_url', 'https://primary-production-d49a.up.railway.app'); 

        // Fetch N8N Cloud Workflows
        $n8nWorkflows = [];
        try {
            $response = $this->n8nService->listWorkflows(); // Using existing method
            $n8nWorkflows = $response['data'] ?? [];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Dashboard: Failed to fetch N8N workflows", ['error' => $e->getMessage()]);
        }

        return Inertia::render('Dashboard', [
            'shop' => $shop,
            'stats' => $stats,
            'executions' => $recentExecutions,
            'n8nUrl' => $n8nUrl,
            'n8nWorkflows' => $n8nWorkflows
        ]);
    }
}
