<?php

use App\Models\AppSetting;
use App\Services\LzkoszLeagueTableService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('lzkosz:sync-table {source?} {season?}', function (LzkoszLeagueTableService $leagueTableService): int {
    $source = $this->argument('source') ?: AppSetting::getValue('league_table_source', 'lzkosz');
    $source = array_key_exists($source, LzkoszLeagueTableService::SOURCES) ? $source : 'lzkosz';
    $configuration = LzkoszLeagueTableService::SOURCES[$source] ?? LzkoszLeagueTableService::SOURCES['lzkosz'];
    $season = $this->argument('season')
        ?: AppSetting::getValue("league_table_season_{$source}")
        ?: AppSetting::getValue('league_table_season')
        ?: $configuration['season'];
    $count = $leagueTableService->sync($source, $season);
    AppSetting::setValue('league_table_source', $source);
    AppSetting::setValue('league_table_season', $season);
    AppSetting::setValue("league_table_season_{$source}", $season);
    $this->info("Tabela {$configuration['label']} została pobrana. Zaktualizowano {$count} drużyn.");

    return self::SUCCESS;
})->purpose('Sync the 3 Liga Mężczyzn table from ŁZKosz');

Schedule::command('lzkosz:sync-table')
    ->dailyAt('16:00')
    ->timezone('Europe/Warsaw')
    ->withoutOverlapping();

Artisan::command('media:sync {source=public} {destination=s3} {--force}', function (string $source, string $destination): int {
    $sourceDisk = Storage::disk($source);
    $destinationDisk = Storage::disk($destination);
    $files = $sourceDisk->allFiles();
    $copied = 0;
    $skipped = 0;
    $failed = 0;

    $this->info("Synchronizacja mediów: {$source} -> {$destination}");
    $bar = $this->output->createProgressBar(count($files));
    $bar->start();

    foreach ($files as $path) {
        if (! $this->option('force') && $destinationDisk->exists($path)) {
            $skipped++;
            $bar->advance();

            continue;
        }

        $stream = $sourceDisk->readStream($path);

        if ($stream === false) {
            $failed++;
            $bar->advance();

            continue;
        }

        try {
            $stored = $destinationDisk->put($path, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        if ($stored === false) {
            $failed++;
        } else {
            $copied++;
        }

        $bar->advance();
    }

    $bar->finish();
    $this->newLine(2);
    $this->info("Skopiowano: {$copied}. Pominięto: {$skipped}. Błędy: {$failed}.");

    return $failed > 0 ? self::FAILURE : self::SUCCESS;
})->purpose('Kopiuje media między dyskami bez usuwania źródła');
