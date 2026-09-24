<?php

return [
    'trusted_proxies' => env('TRUSTED_PROXIES'),

    'password' => [
        'min_length' => (int) env('PASSWORD_MIN_LENGTH', 15),
        'max_length' => (int) env('PASSWORD_MAX_LENGTH', 128),
        'common_phrases' => [
            'password',
            'passwordpassword',
            'adminadminadmin',
            'qwertyqwerty',
            'letmeinletmein',
            'welcomewelcome',
            'administrator',
            'changemechangeme',
        ],
    ],

    'registration_code' => [
        'ttl_minutes' => (int) env('REGISTRATION_CODE_TTL', 10),
        'max_attempts' => (int) env('REGISTRATION_CODE_MAX_ATTEMPTS', 5),
    ],
];
