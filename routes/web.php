<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FlowController;

Route::middleware(['verify.shopify'])->group(function () {
    Route::get('/debug-queue', function () {
        try {
            $jobs = \Illuminate\Support\Facades\DB::table('jobs')->get();
            $failed = \Illuminate\Support\Facades\DB::table('failed_jobs')->get();
            $logs = \App\Models\ExecutionLog::latest()->limit(5)->get();
            
            // Try to run one job
            $output = '';
            if ($jobs->count() > 0) {
                \Illuminate\Support\Facades\Artisan::call('queue:work', ['--once' => true]);
                $output = \Illuminate\Support\Facades\Artisan::output();
            }

            return response()->json([
                'jobs_count' => $jobs->count(),
                'failed_count' => $failed->count(),
                'latest_logs' => $logs,
                'worker_output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });

    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/workflows', [FlowController::class, 'index'])->name('workflows.index');
    Route::post('/workflows/save', [FlowController::class, 'save'])->name('workflows.save');
    Route::get('/workflows/create', [FlowController::class, 'editor'])->name('workflows.create');
    Route::delete('/workflows/{id}', [FlowController::class, 'destroy'])->name('workflows.destroy');
    Route::get('/workflows/{id}', [FlowController::class, 'editor'])->name('editor');
    Route::post('/workflows/{id}/toggle-active', [FlowController::class, 'toggleActive'])->name('workflows.toggle-active');
    
    Route::get('/executions', [\App\Http\Controllers\ExecutionsController::class, 'index'])->name('executions.index');
    Route::get('/executions/{id}', [\App\Http\Controllers\ExecutionsController::class, 'show'])->name('executions.show');
    
    // Connectors
    Route::get('/connectors', [\App\Http\Controllers\ConnectorController::class, 'index'])->name('connectors.index');
});

Route::middleware(['verify.shopify'])->group(function () {
    Route::get('/product-creator', [\App\Http\Controllers\ProductCreationController::class, 'index'])->name('product.creator');
    Route::post('/product/create', [\App\Http\Controllers\ProductCreationController::class, 'create'])->name('product.create');
    Route::get('/product/poll/{jobId}', [\App\Http\Controllers\ProductCreationController::class, 'poll'])->name('product.poll');
});

Route::post('/webhooks/shopify-automation', [\App\Http\Controllers\WebhookController::class, 'handle'])->name('webhooks.handle');
Route::post('/shopify-webhooks/{any}', [\App\Http\Controllers\WebhookController::class, 'handle'])->where('any', '.*');

Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);

// Google Auth Disconnect & Redirect (Needs Shopify Session)
Route::middleware(['verify.shopify'])->group(function () {
    Route::post('/auth/google/disconnect', [\App\Http\Controllers\GoogleAuthController::class, 'disconnect'])->name('auth.google.disconnect');
    Route::get('/auth/google/redirect', [\App\Http\Controllers\GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
});

// Google Auth Callback (Public)
Route::get('/auth/google/callback', [\App\Http\Controllers\GoogleAuthController::class, 'callback'])->name('auth.google.callback');

// make test route
Route::get('/test/shopify', [DashboardController::class, 'handleShopifyCall']);