<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\WorkflowController;

Route::middleware(['verify.shopify'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    // Workflows
    Route::get('/workflows', [WorkflowController::class, 'index'])->name('workflows.index');
    Route::post('/workflows/save', [WorkflowController::class, 'save'])->name('workflows.save');
    Route::get('/workflows/{id?}', [WorkflowController::class, 'editor'])->name('editor');
    Route::delete('/workflows/{id}', [WorkflowController::class, 'destroy'])->name('workflows.destroy');
    
    // New Workflow API routes
    Route::get('/workflows/node-types', [WorkflowController::class, 'nodeTypes'])->name('workflows.node-types');
    Route::get('/workflows/{id}/executions', [WorkflowController::class, 'executions'])->name('workflows.executions');
    Route::post('/workflows/{id}/activate', [WorkflowController::class, 'activate'])->name('workflows.activate');
    Route::post('/workflows/{id}/deactivate', [WorkflowController::class, 'deactivate'])->name('workflows.deactivate');
});

Route::get('/test-n8n', [WorkflowController::class, 'testConnection']);
Route::post('/webhook/{type}', [WebhookController::class, 'handle'])->name('webhook');


Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);