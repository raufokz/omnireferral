<?php

return [

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google_maps' => [
        'key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'google_sheets' => [
        'leads_sheet_url' => env('GOOGLE_SHEETS_LEADS_URL'),
        'leads_csv_url' => env('GOOGLE_SHEETS_LEADS_CSV_URL'),
    ],

    'gohighlevel' => [
        'api_key' => env('GOHIGHLEVEL_API_KEY'),
        'location_id' => env('GOHIGHLEVEL_LOCATION_ID'),
        'base_url' => env('GOHIGHLEVEL_BASE_URL', 'https://services.leadconnectorhq.com'),
        'webhook_secret' => env('GOHIGHLEVEL_WEBHOOK_SECRET'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
    ],

];
