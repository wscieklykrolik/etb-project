<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academy_trainings', function (Blueprint $table): void {
            $table->uuid('recurrence_series_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('academy_trainings', function (Blueprint $table): void {
            $table->dropColumn('recurrence_series_id');
        });
    }
};
