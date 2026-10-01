<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_table_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('season')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('league_table_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('league_table_snapshot_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->string('team_name');
            $table->string('logo_path')->nullable();
            $table->integer('points')->default(0);
            $table->integer('games')->default(0);
            $table->integer('wins')->default(0);
            $table->integer('losses')->default(0);
            $table->integer('home_wins')->default(0);
            $table->integer('home_losses')->default(0);
            $table->integer('away_wins')->default(0);
            $table->integer('away_losses')->default(0);
            $table->integer('points_for')->default(0);
            $table->integer('points_against')->default(0);
            $table->integer('points_difference')->default(0);
            $table->decimal('ratio', 10, 4)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_table_rows');
        Schema::dropIfExists('league_table_snapshots');
    }
};
