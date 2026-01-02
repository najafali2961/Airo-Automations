<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Jobs\CreateProductJob;
use Illuminate\Support\Facades\Cache;

class ProductCreationController extends Controller
{
    public function index()
    {
        return Inertia::render('ProductCreator');
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'sku' => 'nullable|string',
            'quantity' => 'nullable|integer',
            'vendor' => 'nullable|string',
            'type' => 'nullable|string',
        ]);

        $jobId = (string) Str::uuid();
        $shopDomain = auth()->user()->name; 

        // Initial Log
        Cache::put("job_logs_{$jobId}", [
            ['timestamp' => now()->toIso8601String(), 'message' => 'Job dispatched...']
        ], 600);

        CreateProductJob::dispatch($shopDomain, $validated, $jobId);

        return response()->json(['jobId' => $jobId, 'message' => 'Background job started']);
    }

    public function poll($jobId)
    {
        $logs = Cache::get("job_logs_{$jobId}", []);
        return response()->json(['logs' => $logs]);
    }
}
