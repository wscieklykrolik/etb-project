<?php

use Illuminate\Contracts\Console\Kernel;

// Kontrola wyłącznie stagingu; nie wypisuje wartości konfiguracji.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$valid = app()->environment('production')
    && config('app.debug') === false
    && config('app.url') === 'https://test.eattheball.pl'
    && config('database.default') === 'mysql'
    && config('filesystems.media_disk') === 'public'
    && config('filesystems.default') === 'local'
    && in_array(config('mail.default'), ['log', 'smtp'], true)
    && config('queue.default') === 'database'
    && config('session.secure') === true
    && config('session.domain') === null;

$required = ['database.connections.mysql.host', 'database.connections.mysql.database',
    'database.connections.mysql.username', 'database.connections.mysql.password'];
// Log jest świadomie dozwolony tylko na stagingu; poczta nie opuszcza serwera.
if (config('mail.default') === 'smtp') {
    $required = [...$required, 'mail.mailers.smtp.host', 'mail.mailers.smtp.username',
        'mail.mailers.smtp.password', 'mail.from.address'];
}

foreach ($required as $name) {
    $value = config($name);
    $valid = $valid && is_string($value) && $value !== ''
        && ! str_contains($value, 'PLACEHOLDER') && ! str_contains($value, 'example.com');
}

$key = config('app.key');
$firstDeployment = ! file_exists(dirname(base_path(), 2).'/current');
$valid = $valid && (($firstDeployment && ($key === '' || $key === null))
    || (is_string($key) && str_starts_with($key, 'base64:')
        && strlen((string) base64_decode(substr($key, 7), true)) === 32));

if (! $valid) {
    fwrite(STDERR, "Uzupełnij i sprawdź prywatny plik .env stagingu według DEPLOYMENT.md.\n");
    exit(1);
}
