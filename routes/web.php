<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FlowController;

Route::middleware(['verify.shopify'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/workflows', [FlowController::class, 'index'])->name('workflows.index');
    Route::post('/workflows/save', [FlowController::class, 'save'])->name('workflows.save');
    Route::get('/workflows/create', [FlowController::class, 'editor'])->name('workflows.create');
    Route::delete('/workflows/{id}', [FlowController::class, 'destroy'])->name('workflows.destroy');
    Route::get('/workflows/{id}', [FlowController::class, 'editor'])->name('editor');
    Route::post('/workflows/{id}/toggle-active', [FlowController::class, 'toggleActive'])->name('workflows.toggle-active');
    
    Route::get('/executions', [\App\Http\Controllers\ExecutionsController::class, 'index'])->name('executions.index');
    Route::get('/executions/{id}', [\App\Http\Controllers\ExecutionsController::class, 'show'])->name('executions.show');
});

Route::middleware(['verify.shopify'])->group(function () {
    Route::get('/product-creator', [\App\Http\Controllers\ProductCreationController::class, 'index'])->name('product.creator');
    Route::post('/product/create', [\App\Http\Controllers\ProductCreationController::class, 'create'])->name('product.create');
    Route::get('/product/poll/{jobId}', [\App\Http\Controllers\ProductCreationController::class, 'poll'])->name('product.poll');
});

Route::post('/webhooks/shopify-automation', [\App\Http\Controllers\WebhookController::class, 'handle'])->name('webhooks.handle');

Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);