<?php

return [
    'admin_only' => (bool) env('ACCOUNTS_ADMIN_ONLY', env('APP_ENV') === 'production'),
    'admin_login' => 'admin@etb.pl',
];
