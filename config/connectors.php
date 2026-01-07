<?php

return [
    'google' => [
        'title' => 'Google',
        'description' => 'Integrate Gmail, Sheets, and Docs for streamlined automation.',
        'icon' => 'https://cdn-icons-png.flaticon.com/512/2991/2991148.png', 
        'auth_type' => 'oauth',
        'auth_route' => 'auth.google.redirect',
        'connected_check' => 'google_access_token', // Field in User model
        'is_active' => true,
    ],
    'smtp' => [
        'title' => 'SMTP',
        'description' => 'Connect external email hosting provider.',
        'icon' => 'https://cdn-icons-png.flaticon.com/512/732/732200.png',
        'auth_type' => 'basic', // changed from coming_soon
        'is_active' => true,
    ],
    'klaviyo' => [
        'title' => 'Klaviyo',
        'description' => 'Sync Shopify customers and events with Klaviyo.',
        'icon' => 'https://cdn.worldvectorlogo.com/logos/klaviyo.svg',
        'auth_type' => 'oauth',
        'auth_route' => 'klaviyo.auth.url',
        'connected_check' => 'klaviyoCredential',
        'is_active' => true,
    ],
    'slack' => [
        'title' => 'Slack',
        'description' => 'Integrate Shopify events seamlessly with Slack.',
        'icon' => 'https://cdn-icons-png.flaticon.com/512/2111/2111615.png',
        'auth_type' => 'oauth',
        'auth_route' => 'slack.auth.redirect',
        'connected_check' => 'slackCredential',
        'is_active' => true,
    ],
];
