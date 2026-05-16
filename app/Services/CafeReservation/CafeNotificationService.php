<?php

namespace App\Services\CafeReservation;

use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CafeNotificationService
{
    public function notifyReservationCreated(Reservation $reservation, ?Payment $payment = null): array
    {
        return $this->dispatch(
            'reservation_created',
            $reservation,
            'Reservasi baru berhasil dibuat.',
            [
                'payment' => $payment?->only([
                    'id',
                    'payment_code',
                    'type',
                    'amount',
                    'method',
                    'status',
                ]),
            ],
        );
    }

    public function notifyReservationCancelled(Reservation $reservation): array
    {
        return $this->dispatch(
            'reservation_cancelled',
            $reservation,
            'Reservasi telah dibatalkan.',
            [
                'cancellation_reason' => $reservation->cancellation_reason,
            ],
        );
    }

    public function notifyReservationRescheduled(Reservation $reservation, array $previousSchedule): array
    {
        return $this->dispatch(
            'reservation_rescheduled',
            $reservation,
            'Reservasi telah dijadwalkan ulang.',
            [
                'previous_schedule' => $previousSchedule,
            ],
        );
    }

    protected function dispatch(
        string $event,
        Reservation $reservation,
        string $message,
        array $context = [],
    ): array {
        $adminRecipients = User::query()
            ->whereIn('role', [
                UserRole::Admin->value,
                UserRole::Staff->value,
            ])
            ->get(['id', 'name', 'email', 'phone_number', 'role']);

        $payload = [
            'event' => $event,
            'message' => $message,
            'channels' => ['log'],
            'reservation' => [
                'id' => $reservation->id,
                'reservation_code' => $reservation->reservation_code,
                'reservation_date' => $reservation->reservation_date?->toDateString(),
                'start_time' => $reservation->start_time,
                'end_time' => $reservation->end_time,
                'guest_count' => $reservation->guest_count,
                'status' => $reservation->status?->value,
                'table_id' => $reservation->cafe_table_id,
            ],
            'customer' => [
                'user_id' => $reservation->user_id,
                'name' => $reservation->customer_name,
                'phone_number' => $reservation->customer_phone,
            ],
            'admins' => $adminRecipients->map(
                fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'role' => $user->role->value,
                ],
            )->values()->all(),
            'context' => $context,
        ];

        Log::info('Cafe reservation notification dispatched.', $payload);

        return $payload;
    }
}
