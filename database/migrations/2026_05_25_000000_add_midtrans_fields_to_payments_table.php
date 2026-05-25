<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->string('midtrans_order_id')->nullable()->unique()->after('transaction_reference');
            $table->string('snap_token')->nullable()->after('midtrans_order_id');
            $table->string('snap_redirect_url')->nullable()->after('snap_token');
            $table->string('midtrans_status')->nullable()->after('snap_redirect_url');
            $table->json('midtrans_payload')->nullable()->after('midtrans_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropColumn([
                'midtrans_order_id',
                'snap_token',
                'snap_redirect_url',
                'midtrans_status',
                'midtrans_payload',
            ]);
        });
    }
};
