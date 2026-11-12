<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COUNTERS = ['points', 'games', 'wins', 'losses', 'home_wins', 'home_losses', 'away_wins', 'away_losses', 'points_for', 'points_against'];

    public function up(): void
    {
        foreach (['league_standings', 'league_table_rows'] as $name) {
            Schema::table($name, function (Blueprint $table) use ($name): void {
                foreach (self::COUNTERS as $column) {
                    ($name === 'league_standings' ? $table->unsignedInteger($column) : $table->integer($column))->nullable()->default(null)->change();
                }
                $table->integer('points_difference')->nullable()->default(null)->change();
                $table->decimal('ratio', 10, 4)->nullable()->default(null)->change();
                $table->json('last_five')->nullable();
            });
        }
        Schema::table('league_table_snapshots', function (Blueprint $table): void {
            $table->string('source_label')->nullable();
            $table->text('source_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('league_table_snapshots', fn (Blueprint $table) => $table->dropColumn(['source_label', 'source_url']));
        foreach (['league_standings', 'league_table_rows'] as $name) {
            foreach ([...self::COUNTERS, 'points_difference', 'ratio'] as $column) {
                DB::table($name)->whereNull($column)->update([$column => 0]);
            }
            Schema::table($name, function (Blueprint $table) use ($name): void {
                foreach (self::COUNTERS as $column) {
                    if ($name === 'league_standings') {
                        $table->unsignedInteger($column)->nullable(false)->change();
                    } else {
                        $table->integer($column)->nullable(false)->default(0)->change();
                    }
                }
                $table->integer('points_difference')->nullable(false)->default(0)->change();
                $table->decimal('ratio', $name === 'league_standings' ? 8 : 10, 4)->nullable(false)->default(0)->change();
                $table->dropColumn('last_five');
            });
        }
    }
};
