<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsor_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 80);
            $table->string('slug', 40)->unique();
            $table->string('legacy_type', 40)->nullable()->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        $defaultCategories = [
            ['name' => 'Partner strategiczny', 'slug' => 'strategic', 'legacy_type' => 'strategic', 'sort_order' => 10],
            ['name' => 'Sponsorzy', 'slug' => 'sponsor', 'legacy_type' => 'sponsor', 'sort_order' => 20],
            ['name' => 'Partnerzy', 'slug' => 'partner', 'legacy_type' => 'partner', 'sort_order' => 30],
            ['name' => 'Partner technologiczny', 'slug' => 'technology', 'legacy_type' => 'technology', 'sort_order' => 40],
        ];

        foreach ($defaultCategories as $category) {
            DB::table('sponsor_categories')->insert([
                ...$category,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::table('sponsors', function (Blueprint $table): void {
            $table->foreignId('sponsor_category_id')
                ->nullable()
                ->after('type')
                ->constrained('sponsor_categories')
                ->nullOnDelete();
        });

        foreach ($defaultCategories as $category) {
            DB::table('sponsors')
                ->where('type', $category['legacy_type'])
                ->update([
                    'sponsor_category_id' => DB::table('sponsor_categories')->where('legacy_type', $category['legacy_type'])->value('id'),
                ]);
        }

        DB::table('sponsors')
            ->select('type')
            ->whereNull('sponsor_category_id')
            ->orderBy('type')
            ->distinct()
            ->get()
            ->each(function (object $sponsorType) use ($now): void {
                $name = (string) $sponsorType->type;
                $slug = substr(Str::slug($name) ?: 'kategoria', 0, 40);
                $baseSlug = $slug;
                $counter = 2;

                while (DB::table('sponsor_categories')->where('slug', $slug)->exists()) {
                    $suffix = "-{$counter}";
                    $slug = substr($baseSlug, 0, 40 - strlen($suffix)).$suffix;
                    $counter++;
                }

                $categoryId = DB::table('sponsor_categories')->insertGetId([
                    'name' => $name,
                    'slug' => $slug,
                    'legacy_type' => $name,
                    'sort_order' => 100,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('sponsors')
                    ->where('type', $name)
                    ->update(['sponsor_category_id' => $categoryId]);
            });
    }

    public function down(): void
    {
        Schema::table('sponsors', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('sponsor_category_id');
        });

        Schema::dropIfExists('sponsor_categories');
    }
};
