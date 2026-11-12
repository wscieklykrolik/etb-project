<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SETTINGS = [
        'league_table_source' => 'kpzkosz',
        'league_table_season' => '2026/2027',
        'league_table_season_kpzkosz' => '2026/2027',
        'league_table_auto_refresh' => '1',
    ];

    private const BACKUP_KEY = 'league_table_settings_before_kpzkosz';

    private const HOMEPAGE_KEY = 'homepage_league_table_source';

    public function up(): void
    {
        DB::transaction(function (): void {
            $previous = DB::table('app_settings')->whereIn('key', [...array_keys(self::SETTINGS), self::HOMEPAGE_KEY])->pluck('value', 'key');
            DB::table('app_settings')->updateOrInsert(['key' => self::BACKUP_KEY], ['value' => $previous->toJson()]);

            foreach (self::SETTINGS as $key => $value) {
                DB::table('app_settings')->updateOrInsert(['key' => $key], ['value' => $value]);
            }
            if ($previous->get(self::HOMEPAGE_KEY) === 'current:lzkosz') {
                DB::table('app_settings')->where('key', self::HOMEPAGE_KEY)->update(['value' => 'current:kpzkosz']);
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $previous = json_decode(DB::table('app_settings')->where('key', self::BACKUP_KEY)->value('value') ?? '{}', true);
            DB::table('app_settings')->whereIn('key', [...array_keys(self::SETTINGS), self::HOMEPAGE_KEY])->delete();
            foreach ($previous as $key => $value) {
                DB::table('app_settings')->updateOrInsert(['key' => $key], ['value' => $value]);
            }
            DB::table('app_settings')->where('key', self::BACKUP_KEY)->delete();
        });
    }
};
