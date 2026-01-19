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

    // Templates
    Route::get('/templates', [\App\Http\Controllers\TemplateController::class, 'index'])->name('templates.index');
    Route::post('/templates/{template}/activate', [\App\Http\Controllers\TemplateController::class, 'activate'])->name('templates.activate');
    
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
    Route::get('/api/google/auth-url', [\App\Http\Controllers\GoogleAuthController::class, 'generateAuthUrl'])->name('auth.google.url');

    // SMTP
    Route::post('/smtp/save', [\App\Http\Controllers\SmtpController::class, 'store'])->name('smtp.save');
    Route::post('/smtp/test', [\App\Http\Controllers\SmtpController::class, 'test'])->name('smtp.test');
    Route::post('/smtp/disconnect', [\App\Http\Controllers\SmtpController::class, 'disconnect'])->name('smtp.disconnect');
    Route::get('/api/smtp/config', [\App\Http\Controllers\SmtpController::class, 'show'])->name('smtp.show');

    // Slack
    Route::get('/api/slack/auth-url', [\App\Http\Controllers\SlackController::class, 'generateAuthUrl'])->name('slack.auth.url');
    Route::post('/api/slack/disconnect', [\App\Http\Controllers\SlackController::class, 'disconnect'])->name('slack.disconnect');
    // Klaviyo
    Route::get('/api/klaviyo/auth-url', [\App\Http\Controllers\KlaviyoController::class, 'generateAuthUrl'])->name('klaviyo.auth.url');
    Route::post('/api/klaviyo/disconnect', [\App\Http\Controllers\KlaviyoController::class, 'disconnect'])->name('klaviyo.disconnect');
    
    // Universal Resource Fetcher
    Route::get('/api/integrations/{service}/{resource}', [\App\Http\Controllers\IntegrationResourceController::class, 'index'])->name('integrations.resources');
});

// Slack Callback (Public)
Route::get('/slack/callback', [\App\Http\Controllers\SlackController::class, 'callback'])->name('slack.callback');
// Klaviyo Callback
Route::get('/klaviyo/callback', [\App\Http\Controllers\KlaviyoController::class, 'callback'])->name('klaviyo.callback');

// Redirect endpoint - MUST be outside verify.shopify to allow popup opening (params expire), but protected by signed URL
Route::middleware(['signed'])->group(function () {
    Route::get('/auth/google/redirect', [\App\Http\Controllers\GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/slack/auth', [\App\Http\Controllers\SlackController::class, 'redirect'])->name('slack.auth.redirect');
    Route::get('/klaviyo/auth', [\App\Http\Controllers\KlaviyoController::class, 'redirect'])->name('klaviyo.auth.redirect');
});

// Google Auth Callback (Public)
Route::get('/auth/google/callback', [\App\Http\Controllers\GoogleAuthController::class, 'callback'])->name('auth.google.callback');

// make test route
Route::get('/test/shopify', [DashboardController::class, 'handleShopifyCall']);

Route::get('/debug/force-seed', function () {
    try {
        $seeder = new \Database\Seeders\ConnectorsSeeder();
        // Invoke run with mock console output if possible, or just run logic
        // Seeder->command is protected, so we might miss logs unless we mock it or the seeder handles null command
        // My seeder uses $this->command->info(), which might crash if run from controller without a command object.
        // I should fix the seeder to handle null command first?
        // Or just wrap it in a try catch and return "Done".
        // Actually, $this->command is usually available in seeder triggered by Artisan, but here it's null.
        // Let's rely on standard log instead or just run it. 
        // Wait, calling run() directly on a Seeder that uses $this->command will crash if $this->command is null.
        // I need to update the Seeder to allow null command or use Log facade.
        $seeder->setContainer(app());
        $seeder->setCommand(new \Illuminate\Console\Command()); // Mock command
        $seeder->run();
        return "Seeding Completed Successfully. Check Logs.";
    } catch (\Exception $e) {
        return "Seeding Failed: " . $e->getMessage() . "\n" . $e->getTraceAsString();
    }
});

// Admin Tools
Route::middleware(['auth'])->group(function () {
    Route::get('/admin-tools/template-editor/{template:id}', [\App\Http\Controllers\Admin\TemplateEditorController::class, 'edit'])->name('admin.template.editor');
    Route::post('/admin-tools/template-editor/{template:id}/save', [\App\Http\Controllers\Admin\TemplateEditorController::class, 'save'])->name('admin.template.save');
});