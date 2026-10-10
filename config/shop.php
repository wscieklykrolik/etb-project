<?php

return [
    'legacy_enabled' => (bool) env(
        'SHOP_LEGACY_ENABLED',
        in_array(env('APP_ENV', 'production'), ['local', 'testing'], true),
    ),
];
