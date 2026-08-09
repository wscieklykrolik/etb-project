<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table): void {
            $table->string('article_author')->nullable()->after('video_url');
            $table->string('photo_author')->nullable()->after('article_author');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table): void {
            $table->dropColumn(['article_author', 'photo_author']);
        });
    }
};
