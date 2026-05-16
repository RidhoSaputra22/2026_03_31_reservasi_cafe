<?php

namespace App\Services\CafeReservation;

use App\Models\CafeTable;
use Carbon\CarbonInterface;
use RuntimeException;

class CafeTableAssignmentService
{
    public function __construct(
        private readonly CafeAvailabilityService $availabilityService,
    ) {
    }

    public function assignTable(
        CarbonInterface|string $reservationDate,
        string $startTime,
        int $guestCount,
        ?int $excludeReservationId = null,
    ): CafeTable {
        $assignment = $this->previewAssignment(
            $reservationDate,
            $startTime,
            $guestCount,
            $excludeReservationId,
        );

        if (! $assignment['is_available'] || ! $assignment['recommended_table'] instanceof CafeTable) {
            throw new RuntimeException($assignment['reason'] ?? 'Tidak ada meja yang bisa di-assign otomatis.');
        }

        return $assignment['recommended_table'];
    }

    /**
     * @return array{
     *     is_available: bool,
     *     reason: string|null,
     *     reservation_date: string,
     *     start_time: string,
     *     end_time: string|null,
     *     guest_count: int,
     *     slot: \App\Models\ReservationSlot|null,
     *     available_tables: \Illuminate\Database\Eloquent\Collection<int, CafeTable>,
     *     conflicting_reservations: \Illuminate\Database\Eloquent\Collection<int, \App\Models\Reservation>,
     *     recommended_table: CafeTable|null
     * }
     */
    public function previewAssignment(
        CarbonInterface|string $reservationDate,
        string $startTime,
        int $guestCount,
        ?int $excludeReservationId = null,
    ): array {
        $availability = $this->availabilityService->checkAvailability(
            $reservationDate,
            $startTime,
            $guestCount,
            $excludeReservationId,
        );

        $recommendedTable = $availability['available_tables']->first();

        return [
            ...$availability,
            'recommended_table' => $recommendedTable instanceof CafeTable ? $recommendedTable : null,
        ];
    }
}
