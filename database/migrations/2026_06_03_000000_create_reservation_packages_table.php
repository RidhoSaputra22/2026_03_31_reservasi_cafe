<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservation_packages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('aliases')->nullable();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('image_path')->nullable();
            $table->text('summary')->nullable();
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2)->default(0);
            $table->unsignedSmallInteger('included_hours')->default(1);
            $table->decimal('extra_hour_price', 10, 2)->default(0);
            $table->json('facilities')->nullable();
            $table->json('notes')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $featuredSlugs = [
            'coffee-date-corner',
            'work-brew-table',
            'live-music-hangout',
        ];
        $timestamp = now();

        $rows = collect(config('packages', []))
            ->values()
            ->map(function (array $package, int $index) use ($featuredSlugs, $timestamp): array {
                $durationDigits = preg_replace('/\D+/', '', (string) ($package['duration'] ?? '1'));
                $priceDigits = preg_replace('/\D+/', '', (string) ($package['price'] ?? '0'));
                $slug = (string) ($package['slug'] ?? 'package-'.$index);

                return [
                    'slug' => $slug,
                    'aliases' => json_encode(array_values($package['aliases'] ?? []), JSON_THROW_ON_ERROR),
                    'name' => (string) ($package['name'] ?? 'Paket Reservasi'),
                    'category' => $package['category'] ?? null,
                    'image_path' => $package['image'] ?? 'assets/images/hero.jpg',
                    'summary' => $package['summary'] ?? null,
                    'description' => $package['description'] ?? null,
                    'base_price' => (int) ($package['price_amount'] ?? ($priceDigits ?: 0)),
                    'included_hours' => max(1, (int) ($durationDigits ?: 1)),
                    'extra_hour_price' => 0,
                    'facilities' => json_encode(array_values($package['facilities'] ?? []), JSON_THROW_ON_ERROR),
                    'notes' => json_encode(array_values($package['notes'] ?? []), JSON_THROW_ON_ERROR),
                    'is_featured' => in_array($slug, $featuredSlugs, true),
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            })
            ->all();

        if ($rows !== []) {
            DB::table('reservation_packages')->insert($rows);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_packages');
    }
};
