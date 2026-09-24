<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sponsors', fn (Blueprint $table) => $table->string('url', 2048)->change());
        Schema::table('matches', fn (Blueprint $table) => $table->string('ticket_url', 2048)->nullable()->change());
        Schema::table('three_x_three_tournaments', fn (Blueprint $table) => $table->string('registration_url', 2048)->nullable()->change());
        Schema::table('important_pages', fn (Blueprint $table) => $table->longText('body')->nullable()->change());
        Schema::table('app_settings', fn (Blueprint $table) => $table->longText('value')->nullable()->change());
        Schema::table('news', fn (Blueprint $table) => $table->longText('content')->change());
        Schema::table('products', fn (Blueprint $table) => $table->longText('description')->nullable()->change());
    }

    public function down(): void
    {
        // Zwężenie kolumn mogłoby obciąć treści zapisane przez administratora.
        throw new RuntimeException('Automatyczne zwężanie kolumn jest wyłączone, aby chronić dane.');
    }
};
