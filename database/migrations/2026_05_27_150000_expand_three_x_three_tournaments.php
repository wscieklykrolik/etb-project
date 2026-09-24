<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('three_x_three_tournaments', function (Blueprint $table): void {
            $table->string('type')->default('participating');
            $table->string('registration_mode')->default('none');
            $table->string('registration_url')->nullable();
            $table->boolean('registration_enabled')->default(false);
            $table->unsignedTinyInteger('team_size')->nullable();
        });

        Schema::create('three_x_three_tournament_teams', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('three_x_three_tournament_id')->constrained(indexName: '3x3_team_tournament_fk')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('category');
            $table->string('logo_path')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->unique(['three_x_three_tournament_id', 'name'], '3x3_tournament_team_name_unique');
        });

        Schema::create('three_x_three_tournament_team_players', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('three_x_three_tournament_team_id')->constrained(indexName: '3x3_player_team_fk')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('three_x_three_tournament_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('three_x_three_tournament_id')->constrained(indexName: '3x3_group_tournament_fk')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['three_x_three_tournament_id', 'name'], '3x3_tournament_group_name_unique');
        });

        Schema::create('three_x_three_tournament_matches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('three_x_three_tournament_id')->constrained(indexName: '3x3_match_tournament_fk')->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('three_x_three_tournament_groups')->nullOnDelete();
            $table->foreignId('team_one_id')->nullable()->constrained('three_x_three_tournament_teams')->nullOnDelete();
            $table->foreignId('team_two_id')->nullable()->constrained('three_x_three_tournament_teams')->nullOnDelete();
            $table->string('stage')->default('group');
            $table->string('round_label')->nullable();
            $table->unsignedSmallInteger('team_one_score')->nullable();
            $table->unsignedSmallInteger('team_two_score')->nullable();
            $table->dateTime('played_at')->nullable();
            $table->string('court')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('three_x_three_tournament_matches');
        Schema::dropIfExists('three_x_three_tournament_groups');
        Schema::dropIfExists('three_x_three_tournament_team_players');
        Schema::dropIfExists('three_x_three_tournament_teams');

        Schema::table('three_x_three_tournaments', function (Blueprint $table): void {
            $table->dropColumn([
                'type',
                'registration_mode',
                'registration_url',
                'registration_enabled',
                'team_size',
            ]);
        });
    }
};
