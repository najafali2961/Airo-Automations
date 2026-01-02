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
});

Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);