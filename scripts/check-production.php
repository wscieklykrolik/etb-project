<?php

use App\Support\DeploymentConfiguration;
use Illuminate\Contracts\Console\Kernel;

// Kontrola nie odczytuje bazy ani nie wypisuje wartości sekretów.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$firstDeployment = ! file_exists(dirname(base_path(), 2).'/current');
$valid = DeploymentConfiguration::production($app['config'], $firstDeployment, in_array('--require-key', $argv, true));

if (! $valid) {
    fwrite(STDERR, "Uzupełnij i sprawdź prywatny plik .env produkcji według instrukcji publikacji.\n");
    exit(1);
}
