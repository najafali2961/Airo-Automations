<?php
echo "Current Time: " . now()->toDateTimeString() . "\n";
echo "APP_ENV: " . env('APP_ENV') . "\n";
echo "APP_URL (env): " . env('APP_URL') . "\n";
echo "APP_URL (config): " . config('app.url') . "\n\n";

echo "--- Shopify ---\n";
echo "SHOPIFY_API_KEY (env): " . env('SHOPIFY_API_KEY') . "\n";
echo "SHOPIFY_API_KEY (config): " . config('shopify-app.api_key') . "\n";
echo "SHOPIFY_API_REDIRECT (env): " . env('SHOPIFY_API_REDIRECT') . "\n";
echo "SHOPIFY_API_REDIRECT (config): " . config('shopify-app.api_redirect') . "\n";
echo "Authenticate Route: " . (Route::has('authenticate') ? route('authenticate') : 'Not Found') . "\n\n";

echo "--- Slack ---\n";
echo "SLACK_CLIENT_ID (env): " . env('SLACK_CLIENT_ID') . "\n";
echo "SLACK_CLIENT_ID (config): " . config('services.slack.client_id') . "\n";
echo "SLACK_REDIRECT_URI (env): " . env('SLACK_REDIRECT_URI') . "\n";
echo "SLACK_REDIRECT_URI (config): " . config('services.slack.redirect') . "\n";
