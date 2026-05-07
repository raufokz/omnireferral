<?php

return [

    'lead' => [
        'auto_assignment_enabled' => (bool) env('LEAD_AUTO_ASSIGNMENT_ENABLED', false),
        'auto_assignment_strategy' => env('LEAD_AUTO_ASSIGNMENT_STRATEGY', 'round_robin'),
    ],

    'company' => [
        'support_email' => env('OMNI_SUPPORT_EMAIL', 'hello@omnireferral.us'),
        /** E.164 for tel: links, e.g. +12125551000. Omit to hide phone CTAs. */
        'support_phone_e164' => env('OMNI_SUPPORT_PHONE_E164'),
        /** Human-readable phone for display; omit when no phone. */
        'support_phone_display' => env('OMNI_SUPPORT_PHONE_DISPLAY'),
        'hq_location_label' => env('OMNI_HQ_LOCATION_LABEL', 'New York, NY'),
        /** Query string used in public Google Maps embeds (no API key required). */
        'maps_embed_query' => env('OMNI_MAPS_EMBED_QUERY', 'New York, NY'),
        'office_hours' => env('OMNI_OFFICE_HOURS', 'Mon-Fri, 9am-6pm ET'),
    ],

];
