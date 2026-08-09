<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_filter_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('product_filter_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_filter_group_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_filter_group_id', 'slug']);
        });

        Schema::create('product_filter_option_product', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_filter_option_id')->constrained()->cascadeOnDelete();

            $table->primary(['product_id', 'product_filter_option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_filter_option_product');
        Schema::dropIfExists('product_filter_options');
        Schema::dropIfExists('product_filter_groups');
    }
};
