<?php
echo "APP_URL: " . config('app.url') . "\n";
echo "SHOPIFY_API_KEY: " . config('shopify-app.api_key') . "\n";
echo "SHOPIFY_API_REDIRECT: " . config('shopify-app.api_redirect') . "\n";
echo "Route authenticate: " . (Route::has('authenticate') ? route('authenticate') : 'Route not found') . "\n";
