<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('package_slug');
            $table->string('guest_name');
            $table->unsignedTinyInteger('rating');
            $table->text('comment');
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['package_slug', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_reviews');
    }
};
