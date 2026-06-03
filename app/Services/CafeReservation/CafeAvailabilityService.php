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
        ?int $durationHours = null,
    ): array {
        $date = $this->normalizeDate($reservationDate);
        $startTime = $this->normalizeTime($startTime);
        $resolvedDurationHours = max(1, (int) ($durationHours ?? 1));
        $resolvedEndTime = $this->resolveEndTimeForDuration($startTime, $resolvedDurationHours);
        $slot = $resolvedEndTime === null
            ? null
            : $this->findSlot($date, $startTime, $resolvedEndTime);

        if ($slot === null) {
            return [
                'is_available' => false,
                'reason' => $this->unavailableScheduleReason($date, $startTime, $resolvedEndTime),
                'reservation_date' => $date->toDateString(),
                'start_time' => $startTime,
                'end_time' => $resolvedEndTime,
                'guest_count' => $guestCount,
                'slot' => null,
                'available_tables' => new Collection(),
                'conflicting_reservations' => new Collection(),
            ];
        }

        $conflictingReservations = $this->getConflictingReservations(
            $date,
            $startTime,
            $resolvedEndTime,
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
                : 'Tidak ada meja aktif yang tersedia untuk jam dan jumlah tamu tersebut.',
            'reservation_date' => $date->toDateString(),
            'start_time' => $startTime,
            'end_time' => $resolvedEndTime,
            'guest_count' => $guestCount,
            'slot' => $slot,
            'available_tables' => $availableTables,
            'conflicting_reservations' => $conflictingReservations,
        ];
    }

    public function findSlot(
        CarbonInterface|string $reservationDate,
        string $startTime,
        ?string $endTime = null,
    ): ?ReservationSlot {
        $date = $this->normalizeDate($reservationDate);
        $startTime = $this->normalizeTime($startTime);
        $endTime = $endTime === null ? null : $this->normalizeTime($endTime);

        return ReservationSlot::query()
            ->where('day_of_week', $date->dayOfWeek)
            ->where('is_active', true)
            ->where('start_time', '<=', $startTime)
            ->when(
                $endTime !== null,
                fn ($query) => $query->where('end_time', '>=', $endTime),
            )
            ->orderBy('start_time')
            ->first();
    }

    /**
     * @return Collection<int, ReservationSlot>
     */
    public function activeSlotsForDate(CarbonInterface|string $reservationDate): Collection
    {
        $date = $this->normalizeDate($reservationDate);

        return ReservationSlot::query()
            ->where('day_of_week', $date->dayOfWeek)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();
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

    protected function unavailableScheduleReason(
        CarbonInterface $date,
        string $startTime,
        ?string $endTime,
    ): string {
        $activeSlots = $this->activeSlotsForDate($date);

        if ($activeSlots->isEmpty()) {
            return 'Belum ada rentang jam reservasi aktif pada hari tersebut.';
        }

        if ($endTime === null) {
            return 'Durasi reservasi tidak valid.';
        }

        $activeLabels = $activeSlots
            ->map(fn (ReservationSlot $slot): string => substr($slot->start_time, 0, 5).'-'.substr((string) $slot->end_time, 0, 5))
            ->implode(', ');

        return 'Jam reservasi harus berada di dalam rentang aktif: '.$activeLabels.'.';
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

    public function resolveEndTimeForDuration(string $startTime, int $durationHours): ?string
    {
        if ($durationHours <= 0) {
            return null;
        }

        return Carbon::parse($this->normalizeTime($startTime))
            ->addHours($durationHours)
            ->format('H:i:s');
    }
}
