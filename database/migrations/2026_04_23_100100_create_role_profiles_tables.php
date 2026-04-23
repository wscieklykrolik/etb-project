<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('athlete_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('performance_parameters')->nullable();
            $table->json('playbook')->nullable();
            $table->json('opponents_intel')->nullable();
            $table->timestamps();
        });

        Schema::create('fan_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('can_buy_tickets')->default(true);
            $table->boolean('can_buy_merch')->default(true);
            $table->timestamps();
        });

        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('can_manage_articles')->default(true);
            $table->boolean('can_write_database')->default(true);
            $table->boolean('can_read_database')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_profiles');
        Schema::dropIfExists('fan_profiles');
        Schema::dropIfExists('athlete_profiles');
    }
};
