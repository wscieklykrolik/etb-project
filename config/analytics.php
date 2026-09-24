<?php

return [
    'google' => [
        'measurement_id' => env('GOOGLE_ANALYTICS_MEASUREMENT_ID'),
        'debug' => (bool) env('GOOGLE_ANALYTICS_DEBUG', false),
    ],
];
