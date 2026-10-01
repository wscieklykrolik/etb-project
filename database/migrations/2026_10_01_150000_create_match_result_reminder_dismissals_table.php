<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_result_reminder_dismissals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'match_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_result_reminder_dismissals');
    }
};
