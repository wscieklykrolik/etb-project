<?php

return [
    // Staging zachowuje zgodność z już istniejącym plikiem .env na Hostingerze.
    'environment' => env('DEPLOYMENT_ENVIRONMENT', 'staging'),
];
