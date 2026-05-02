<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_games', function (Blueprint $table) {
            $table->id();
            $table->string('opponent');
            $table->dateTime('match_date');
            $table->string('location');
            $table->string('result')->nullable();
            $table->dateTime('publish_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('match_games'); }
};
