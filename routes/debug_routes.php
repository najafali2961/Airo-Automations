<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IntegrationResourceController;

Route::get('/test-resource/{service}/{resource}', function ($service, $resource) {
    return [
        'received_service' => $service,
        'received_resource' => $resource,
        'service_lower' => strtolower($service),
        'match_slack' => $service === 'slack',
        'match_klaviyo' => $service === 'klaviyo',
    ];
});
