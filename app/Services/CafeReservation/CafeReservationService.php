<?php

namespace App\Services\CafeReservation;

use App\Enums\ReservationStatus;
use App\Enums\TableStatus;
use App\Models\CafeTable;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\ReservationSlot;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class CafeReservationService
{
    public function __construct(
        private readonly CafeAvailabilityService $availabilityService,
        private readonly CafeTableAssignmentService $tableAssignmentService,
        private readonly CafePaymentService $paymentService,
        private readonly CafeNotificationService $notificationService,
    ) {
    }

    /**
     * @return array{
     *     reservation: Reservation,
     *     table: CafeTable,
     *     slot: ReservationSlot,
     *     payment: Payment|null,
     *     notifications: array<int, array<string, mixed>>
     * }
     */
    public function createReservation(array $data): array
    {
        $userId = $this->requireInt($data, 'user_id');
        $customerName = $this->requireString($data, 'customer_name');
        $reservationDate = $this->normalizeDate($this->requireString($data, 'reservation_date'));
        $startTime = $this->normalizeTime($this->requireString($data, 'start_time'));
        $guestCount = $this->requireInt($data, 'guest_count');
        $slot = $this->resolveSlot($reservationDate, $startTime);
        $table = $this->resolveTable($data, $reservationDate, $slot->start_time, $guestCount);

        return DB::transaction(function () use (
            $customerName,
            $data,
            $guestCount,
            $reservationDate,
            $slot,
            $startTime,
            $table,
            $userId,
        ): array {
            $reservation = Reservation::query()->create([
                'reservation_code' => $this->generateReservationCode(),
                'user_id' => $userId,
                'cafe_table_id' => $table->id,
                'reservation_slot_id' => $slot->id,
                'package_slug' => $data['package_slug'] ?? null,
                'package_name' => $data['package_name'] ?? null,
                'customer_name' => $customerName,
                'customer_phone' => $data['customer_phone'] ?? null,
                'reservation_date' => $reservationDate->toDateString(),
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'guest_count' => $guestCount,
                'notes' => $data['notes'] ?? null,
                'amount_due' => 0,
                'status' => ReservationStatus::PendingPayment,
            ]);

            $payment = $this->paymentService->createPaymentForReservation(
                $reservation,
                is_array($data['payment'] ?? null) ? $data['payment'] : [],
            );

            $table->forceFill(['status' => TableStatus::Reserved])->save();

            $reservation = $this->loadReservation($reservation);

            return [
                'reservation' => $reservation,
                'table' => $table->fresh(),
                'slot' => $slot,
                'payment' => $payment?->fresh(),
                'notifications' => [
                    $this->notificationService->notifyReservationCreated($reservation, $payment),
                ],
            ];
        });
    }

    /**
     * @return array{
     *     reservation: Reservation,
     *     notifications: array<int, array<string, mixed>>
     * }
     */
    public function cancelReservation(
        Reservation $reservation,
        ?string $cancellationReason = null,
        ?User $actor = null,
    ): array {
        $this->ensureReservationCanBeModified($reservation, 'dibatalkan');

        return DB::transaction(function () use ($actor, $cancellationReason, $reservation): array {
            $reservation->forceFill([
                'status' => ReservationStatus::Cancelled,
                'cancelled_at' => now(),
                'cancellation_reason' => $cancellationReason,
                'cancelled_by' => $actor?->id,
            ])->save();

            $this->refreshTableStatus($reservation->cafeTable()->first());

            $reservation = $this->loadReservation($reservation);

            return [
                'reservation' => $reservation,
                'notifications' => [
                    $this->notificationService->notifyReservationCancelled($reservation),
                ],
            ];
        });
    }

    /**
     * @return array{
     *     reservation: Reservation,
     *     table: CafeTable,
     *     slot: ReservationSlot,
     *     payment: Payment|null,
     *     notifications: array<int, array<string, mixed>>
     * }
     */
    public function rescheduleReservation(Reservation $reservation, array $data): array
    {
        $this->ensureReservationCanBeModified($reservation, 'dijadwalkan ulang');

        $currentDate = $reservation->reservation_date instanceof CarbonInterface
            ? $reservation->reservation_date->copy()
            : Carbon::parse($reservation->reservation_date);

        $reservationDate = $this->normalizeDate($data['reservation_date'] ?? $currentDate->toDateString());
        $startTime = $this->normalizeTime((string) ($data['start_time'] ?? $reservation->start_time));
        $guestCount = (int) ($data['guest_count'] ?? $reservation->guest_count);
        $slot = $this->resolveSlot($reservationDate, $startTime);
        $table = $this->resolveTable($data, $reservationDate, $slot->start_time, $guestCount, $reservation->id);
        $previousSchedule = [
            'reservation_date' => $currentDate->toDateString(),
            'start_time' => $reservation->start_time,
            'end_time' => $reservation->end_time,
            'table_id' => $reservation->cafe_table_id,
            'guest_count' => $reservation->guest_count,
        ];

        return DB::transaction(function () use ($data, $guestCount, $previousSchedule, $reservation, $reservationDate, $slot, $startTime, $table): array {
            $oldTable = $reservation->cafeTable()->first();

            $reservation->forceFill([
                'cafe_table_id' => $table->id,
                'reservation_slot_id' => $slot->id,
                'package_slug' => $data['package_slug'] ?? $reservation->package_slug,
                'package_name' => $data['package_name'] ?? $reservation->package_name,
                'customer_name' => $data['customer_name'] ?? $reservation->customer_name,
                'customer_phone' => $data['customer_phone'] ?? $reservation->customer_phone,
                'reservation_date' => $reservationDate->toDateString(),
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'guest_count' => $guestCount,
                'notes' => $data['notes'] ?? $reservation->notes,
            ])->save();

            $payment = null;

            if (is_array($data['payment'] ?? null)) {
                $payment = $this->paymentService->createPaymentForReservation($reservation, $data['payment']);
            }

            $table->forceFill(['status' => TableStatus::Reserved])->save();

            if ($oldTable instanceof CafeTable && $oldTable->id !== $table->id) {
                $this->refreshTableStatus($oldTable);
            }

            $reservation = $this->loadReservation($reservation);

            return [
                'reservation' => $reservation,
                'table' => $table->fresh(),
                'slot' => $slot,
                'payment' => $payment?->fresh(),
                'notifications' => [
                    $this->notificationService->notifyReservationRescheduled($reservation, $previousSchedule),
                ],
            ];
        });
    }

    protected function resolveSlot(CarbonInterface|string $reservationDate, string $startTime): ReservationSlot
    {
        $slot = $this->availabilityService->findSlot($reservationDate, $startTime);

        if (! $slot instanceof ReservationSlot) {
            throw new RuntimeException('Slot reservasi yang dipilih tidak tersedia.');
        }

        return $slot;
    }

    protected function resolveTable(
        array $data,
        CarbonInterface|string $reservationDate,
        string $startTime,
        int $guestCount,
        ?int $excludeReservationId = null,
    ): CafeTable {
        if (isset($data['cafe_table_id'])) {
            $table = CafeTable::query()
                ->whereKey($data['cafe_table_id'])
                ->where('is_active', true)
                ->firstOrFail();

            if ($table->capacity < $guestCount) {
                throw new RuntimeException('Kapasitas meja yang dipilih tidak cukup untuk jumlah tamu.');
            }

            $availability = $this->availabilityService->checkAvailability(
                $reservationDate,
                $startTime,
                $guestCount,
                $excludeReservationId,
            );

            $isTableAvailable = $availability['available_tables']
                ->pluck('id')
                ->contains($table->id);

            if (! $isTableAvailable) {
                throw new RuntimeException('Meja yang dipilih tidak tersedia pada slot tersebut.');
            }

            return $table;
        }

        return $this->tableAssignmentService->assignTable(
            $reservationDate,
            $startTime,
            $guestCount,
            $excludeReservationId,
        );
    }

    protected function ensureReservationCanBeModified(Reservation $reservation, string $action): void
    {
        if ($reservation->status === ReservationStatus::Cancelled) {
            throw new RuntimeException("Reservasi yang sudah dibatalkan tidak bisa {$action} lagi.");
        }

        if ($reservation->status === ReservationStatus::Completed) {
            throw new RuntimeException("Reservasi yang sudah selesai tidak bisa {$action} lagi.");
        }

        if ($reservation->status === ReservationStatus::CheckedIn) {
            throw new RuntimeException("Reservasi yang sudah check-in tidak bisa {$action} lagi.");
        }
    }

    protected function refreshTableStatus(?CafeTable $table): void
    {
        if (! $table instanceof CafeTable) {
            return;
        }

        $hasActiveReservation = Reservation::query()
            ->where('cafe_table_id', $table->id)
            ->whereNotIn('status', [
                ReservationStatus::Cancelled->value,
                ReservationStatus::Completed->value,
            ])
            ->exists();

        $table->forceFill([
            'status' => $hasActiveReservation
                ? TableStatus::Reserved
                : TableStatus::Available,
        ])->save();
    }

    protected function loadReservation(Reservation $reservation): Reservation
    {
        return $reservation->fresh([
            'user',
            'cafeTable',
            'reservationSlot',
            'payments',
        ]);
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

    protected function requireString(array $data, string $key): string
    {
        $value = trim((string) ($data[$key] ?? ''));

        if ($value === '') {
            throw new InvalidArgumentException("Field {$key} wajib diisi.");
        }

        return $value;
    }

    protected function requireInt(array $data, string $key): int
    {
        if (! isset($data[$key]) || ! is_numeric($data[$key])) {
            throw new InvalidArgumentException("Field {$key} wajib berupa angka.");
        }

        $value = (int) $data[$key];

        if ($value <= 0) {
            throw new InvalidArgumentException("Field {$key} harus lebih besar dari 0.");
        }

        return $value;
    }

    protected function generateReservationCode(): string
    {
        do {
            $code = 'RSV-'.Str::upper(Str::random(8));
        } while (Reservation::query()->where('reservation_code', $code)->exists());

        return $code;
    }
}
