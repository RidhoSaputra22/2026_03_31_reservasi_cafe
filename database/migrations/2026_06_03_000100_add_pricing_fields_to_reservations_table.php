<?php

use Carbon\Carbon;
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
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('reservation_package_id')
                ->nullable()
                ->after('reservation_slot_id')
                ->constrained('reservation_packages')
                ->nullOnDelete();
            $table->unsignedSmallInteger('duration_hours')->default(1)->after('end_time');
            $table->decimal('total_price', 10, 2)->default(0)->after('amount_due');
        });

        $packageIds = DB::table('reservation_packages')
            ->pluck('id', 'slug');
        $packagePrices = DB::table('reservation_packages')
            ->pluck('base_price', 'slug');

        DB::table('reservations')
            ->orderBy('id')
            ->get()
            ->each(function (object $reservation) use ($packageIds, $packagePrices): void {
                $startTime = Carbon::parse((string) $reservation->start_time);
                $endTime = $reservation->end_time !== null
                    ? Carbon::parse((string) $reservation->end_time)
                    : null;
                $durationHours = $endTime instanceof Carbon
                    ? max(1, (int) floor($startTime->diffInMinutes($endTime, false) / 60))
                    : 1;
                $packageSlug = (string) ($reservation->package_slug ?? '');
                $matchedPackageId = $packageIds[$packageSlug] ?? null;
                $matchedPackagePrice = $packagePrices[$packageSlug] ?? null;

                DB::table('reservations')
                    ->where('id', $reservation->id)
                    ->update([
                        'reservation_package_id' => $matchedPackageId,
                        'duration_hours' => $durationHours,
                        'total_price' => $matchedPackagePrice !== null
                            ? (float) $matchedPackagePrice
                            : (float) $reservation->amount_due,
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reservation_package_id');
            $table->dropColumn(['duration_hours', 'total_price']);
        });
    }
};
