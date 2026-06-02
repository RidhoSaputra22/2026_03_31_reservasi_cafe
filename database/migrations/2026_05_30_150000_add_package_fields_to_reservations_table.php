<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table): void {
            $table->string('package_slug')->nullable()->after('reservation_slot_id');
            $table->string('package_name')->nullable()->after('package_slug');

            $table->index('package_slug');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table): void {
            $table->dropIndex(['package_slug']);
            $table->dropColumn(['package_slug', 'package_name']);
        });
    }
};
