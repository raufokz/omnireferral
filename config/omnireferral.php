<?php

return [
    'security' => [
        // When true, users with Spatie role name "Super Admin" bypass all gates.
        // Recommended: keep false in production and rely on users.is_super_admin for break-glass.
        'allow_spatie_super_admin' => (bool) env('OMNI_ALLOW_SPATIE_SUPER_ADMIN', false),
    ],

    'lead' => [
        'auto_assignment_enabled' => (bool) env('LEAD_AUTO_ASSIGNMENT_ENABLED', false),
        'auto_assignment_strategy' => env('LEAD_AUTO_ASSIGNMENT_STRATEGY', 'round_robin'),
    ],
    'company' => [
        'support_email' => env('OMNI_SUPPORT_EMAIL', 'hello@omnireferrals.com'),
        'support_phone_e164' => env('OMNI_SUPPORT_PHONE_E164'),
        'support_phone_display' => env('OMNI_SUPPORT_PHONE_DISPLAY'),
        'hq_location_label' => env('OMNI_HQ_LOCATION_LABEL', 'New York, NY'),
        'maps_embed_query' => env('OMNI_MAPS_EMBED_QUERY', 'New York, NY'),
        'office_hours' => env('OMNI_OFFICE_HOURS', 'Mon-Fri, 9am-6pm ET'),
    ],
];
