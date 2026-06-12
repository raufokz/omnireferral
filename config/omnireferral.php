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
        'support_email' => env('OMNI_SUPPORT_EMAIL', 'support@omnireferrals.com'),
        'info_email' => env('OMNI_INFO_EMAIL', 'info@omnireferrals.com'),
        'support_phone_e164' => env('OMNI_SUPPORT_PHONE_E164', '+12312813131'),
        'support_phone_display' => env('OMNI_SUPPORT_PHONE_DISPLAY', '+1 231-281-3131'),
        'hq_location_label' => env('OMNI_HQ_LOCATION_LABEL', 'Salem, MA, USA'),
        'maps_embed_query' => env('OMNI_MAPS_EMBED_QUERY', 'Salem, MA, USA'),
        'office_hours' => env('OMNI_OFFICE_HOURS', 'Mon-Fri, 9am-6pm ET'),
        'hq_address' => [
            'street_address' => env('OMNI_HQ_ADDRESS_STREET', ''),
            'locality' => env('OMNI_HQ_ADDRESS_LOCALITY', 'Salem'),
            'region' => env('OMNI_HQ_ADDRESS_REGION', 'MA'),
            'postal_code' => env('OMNI_HQ_ADDRESS_POSTAL_CODE', ''),
            'country' => env('OMNI_HQ_ADDRESS_COUNTRY', 'US'),
        ],
        'social_links' => [
            'facebook' => env('OMNI_FACEBOOK_URL', 'https://www.facebook.com/profile.php?id=61589808382458'),
            'instagram' => env('OMNI_INSTAGRAM_URL', 'https://www.instagram.com/omni.referral/'),
            'linkedin' => env('OMNI_LINKEDIN_URL', 'https://www.linkedin.com/company/omnireferral/'),
            'pinterest' => env('OMNI_PINTEREST_URL', 'https://www.pinterest.com/omnireferral/'),
        ],
    ],
];
