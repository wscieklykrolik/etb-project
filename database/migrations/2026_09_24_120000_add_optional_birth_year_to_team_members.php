<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table): void {
            $table->date('date_of_birth')->nullable()->change();
        });

        foreach (['players', 'team_staff', 'three_x_three_members'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->unsignedSmallInteger('birth_year')->nullable();
            });
        }

        DB::table('players')->whereNotNull('date_of_birth')->orderBy('id')->chunkById(500, function ($players): void {
            foreach ($players as $player) {
                DB::table('players')->where('id', $player->id)->update([
                    'birth_year' => (int) substr($player->date_of_birth, 0, 4),
                ]);
            }
        });
    }

    public function down(): void
    {
        foreach (['players', 'team_staff', 'three_x_three_members'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn('birth_year');
            });
        }

        // Dates stay nullable: a year alone cannot restore a person's full birth date.
    }
};
