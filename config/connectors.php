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
    'twilio' => [
        'title' => 'Twilio',
        'description' => 'Send messages through SMS or WhatsApp.',
        'icon' => 'https://cdn-icons-png.flaticon.com/512/5968/5968841.png',
        'auth_type' => 'coming_soon',
        'is_active' => false,
    ],
    'slack' => [
        'title' => 'Slack',
        'description' => 'Integrate Shopify events seamlessly with Slack.',
        'icon' => 'https://cdn-icons-png.flaticon.com/512/2111/2111615.png',
        'auth_type' => 'coming_soon',
        'is_active' => false,
    ],
];
