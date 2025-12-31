<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WebhookController;

Route::middleware(['verify.shopify'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
});

Route::post('/webhook/{type}', [WebhookController::class, 'handle'])->name('webhook');
