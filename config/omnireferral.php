<?php

return [

    'lead' => [
        'auto_assignment_enabled' => (bool) env('LEAD_AUTO_ASSIGNMENT_ENABLED', false),
        'auto_assignment_strategy' => env('LEAD_AUTO_ASSIGNMENT_STRATEGY', 'round_robin'),
    ],

];
