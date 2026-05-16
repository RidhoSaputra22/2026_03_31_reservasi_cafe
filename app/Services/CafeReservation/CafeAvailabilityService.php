<?php

namespace App\Services\CafeReservation;

use App\Enums\ReservationStatus;
use App\Models\CafeTable;
use App\Models\Reservation;
use App\Models\ReservationSlot;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

class CafeAvailabilityService
{
    /**
     * @return array{
     *     is_available: bool,
     *     reason: string|null,
     *     reservation_date: string,
     *     start_time: string,
     *     end_time: string|null,
     *     guest_count: int,
     *     slot: ReservationSlot|null,
     *     available_tables: Collection<int, CafeTable>,
     *     conflicting_reservations: Collection<int, Reservation>
     * }
     */
    public function checkAvailability(
        CarbonInterface|string $reservationDate,
        string $startTime,
        int $guestCount,
        ?int $excludeReservationId = null,
    ): array {
        $date = $this->normalizeDate($reservationDate);
        $startTime = $this->normalizeTime($startTime);
        $slot = $this->findSlot($date, $startTime);

        if (! $slot instanceof ReservationSlot) {
            return [
                'is_available' => false,
                'reason' => 'Slot reservasi tidak ditemukan atau sedang nonaktif.',
                'reservation_date' => $date->toDateString(),
                'start_time' => $startTime,
                'end_time' => null,
                'guest_count' => $guestCount,
                'slot' => null,
                'available_tables' => new Collection(),
                'conflicting_reservations' => new Collection(),
            ];
        }

        $conflictingReservations = $this->getConflictingReservations(
            $date,
            $slot->start_time,
            $slot->end_time,
            $excludeReservationId,
        );

        $availableTables = $this->getAvailableTables(
            $guestCount,
            $conflictingReservations->pluck('cafe_table_id')->filter()->all(),
        );

        return [
            'is_available' => $availableTables->isNotEmpty(),
            'reason' => $availableTables->isNotEmpty()
                ? null
                : 'Tidak ada meja aktif yang tersedia untuk slot dan jumlah tamu tersebut.',
            'reservation_date' => $date->toDateString(),
            'start_time' => $slot->start_time,
            'end_time' => $slot->end_time,
            'guest_count' => $guestCount,
            'slot' => $slot,
            'available_tables' => $availableTables,
            'conflicting_reservations' => $conflictingReservations,
        ];
    }

    public function findSlot(CarbonInterface|string $reservationDate, string $startTime): ?ReservationSlot
    {
        $date = $this->normalizeDate($reservationDate);
        $startTime = $this->normalizeTime($startTime);

        return ReservationSlot::query()
            ->where('day_of_week', $date->dayOfWeek)
            ->where('start_time', $startTime)
            ->where('is_active', true)
            ->first();
    }

    /**
     * @param  array<int, int>  $bookedTableIds
     * @return Collection<int, CafeTable>
     */
    public function getAvailableTables(int $guestCount, array $bookedTableIds = []): Collection
    {
        return CafeTable::query()
            ->where('is_active', true)
            ->where('capacity', '>=', $guestCount)
            ->when(
                $bookedTableIds !== [],
                fn ($query) => $query->whereNotIn('id', $bookedTableIds),
            )
            ->orderBy('capacity')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getConflictingReservations(
        CarbonInterface|string $reservationDate,
        string $startTime,
        ?string $endTime,
        ?int $excludeReservationId = null,
    ): Collection {
        $date = $this->normalizeDate($reservationDate);
        $startTime = $this->normalizeTime($startTime);
        $endTime = $endTime === null ? null : $this->normalizeTime($endTime);

        return Reservation::query()
            ->with('cafeTable')
            ->whereDate('reservation_date', $date->toDateString())
            ->whereNotIn('status', [
                ReservationStatus::Cancelled->value,
                ReservationStatus::Completed->value,
            ])
            ->when(
                $excludeReservationId !== null,
                fn ($query) => $query->whereKeyNot($excludeReservationId),
            )
            ->where(function ($query) use ($startTime, $endTime): void {
                if ($endTime === null) {
                    $query->where('start_time', '<=', $startTime)
                        ->where(function ($nestedQuery) use ($startTime): void {
                            $nestedQuery->whereNull('end_time')
                                ->orWhere('end_time', '>', $startTime);
                        });

                    return;
                }

                $query->where('start_time', '<', $endTime)
                    ->where(function ($nestedQuery) use ($startTime): void {
                        $nestedQuery->whereNull('end_time')
                            ->orWhere('end_time', '>', $startTime);
                    });
            })
            ->get();
    }

    protected function normalizeDate(CarbonInterface|string $reservationDate): CarbonInterface
    {
        return $reservationDate instanceof CarbonInterface
            ? $reservationDate->copy()->startOfDay()
            : Carbon::parse($reservationDate)->startOfDay();
    }

    protected function normalizeTime(string $time): string
    {
        return Carbon::parse($time)->format('H:i:s');
    }
}
