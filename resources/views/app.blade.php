<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="shopify-api-key" content="{{ env('SHOPIFY_API_KEY') }}" />
    <script src="https://cdn.shopify.com/shopifycloud/app-bridge.js" data-api-key="{{ env('SHOPIFY_API_KEY') }}"></script>
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @inertiaHead
</head>

<body>
    @inertia
</body>

</html>
