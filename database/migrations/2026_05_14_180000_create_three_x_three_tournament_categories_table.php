<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('three_x_three_tournament_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('three_x_three_tournament_id')->constrained(indexName: '3x3_category_tournament_fk')->cascadeOnDelete();
            $table->string('category');
            $table->timestamps();

            $table->unique(['three_x_three_tournament_id', 'category'], '3x3_tournament_category_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('three_x_three_tournament_categories');
    }
};
