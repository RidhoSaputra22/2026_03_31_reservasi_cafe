<?php

namespace App\Services\CafeReservation;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\ReservationStatus;
use App\Models\CafeProfile;
use App\Models\Payment;
use App\Models\Reservation;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

class CafePaymentService
{
    public function createPaymentForReservation(Reservation $reservation, array $paymentData = []): ?Payment
    {
        $paymentType = $this->resolvePaymentType($paymentData);

        if (! $paymentType instanceof PaymentType) {
            $reservation->forceFill([
                'amount_due' => 0,
                'status' => ReservationStatus::Confirmed,
                'confirmed_at' => $reservation->confirmed_at ?? now(),
            ])->save();

            return null;
        }

        $amount = $this->resolveAmount($reservation, $paymentType, $paymentData['amount'] ?? null);

        if ($amount <= 0) {
            $reservation->forceFill([
                'amount_due' => 0,
                'status' => ReservationStatus::Confirmed,
                'confirmed_at' => $reservation->confirmed_at ?? now(),
            ])->save();

            return null;
        }

        $status = $this->resolvePaymentStatus($paymentData['status'] ?? PaymentStatus::Pending);
        $method = $this->resolvePaymentMethod($paymentData['method'] ?? PaymentMethod::Cash);
        $paidAt = $this->resolvePaidAt($status, $paymentData['paid_at'] ?? null);
        $verifiedAt = $status === PaymentStatus::Paid
            ? $this->resolveDateTime($paymentData['verified_at'] ?? $paidAt)
            : null;

        $payment = $reservation->payments()->create([
            'payment_code' => $this->generatePaymentCode(),
            'type' => $paymentType,
            'amount' => $amount,
            'method' => $method,
            'status' => $status,
            'transaction_reference' => $paymentData['transaction_reference'] ?? null,
            'proof_path' => $paymentData['proof_path'] ?? null,
            'paid_at' => $paidAt,
            'verified_at' => $verifiedAt,
            'verified_by' => $paymentData['verified_by'] ?? null,
            'notes' => $paymentData['notes'] ?? null,
        ]);

        $reservationAttributes = ['amount_due' => $amount];

        if ($status === PaymentStatus::Paid) {
            $reservationAttributes['status'] = ReservationStatus::Confirmed;
            $reservationAttributes['confirmed_at'] = $reservation->confirmed_at ?? now();
        } elseif ($status === PaymentStatus::AwaitingVerification) {
            $reservationAttributes['status'] = ReservationStatus::AwaitingConfirmation;
            $reservationAttributes['confirmed_at'] = null;
        } else {
            $reservationAttributes['status'] = ReservationStatus::PendingPayment;
            $reservationAttributes['confirmed_at'] = null;
        }

        $reservation->forceFill($reservationAttributes)->save();

        return $payment->fresh();
    }

    public function resolveDefaultAmount(PaymentType $paymentType): float
    {
        $profile = CafeProfile::query()->first();

        if ($paymentType === PaymentType::DownPayment) {
            return (float) ($profile?->down_payment_amount ?? 0);
        }

        return 0;
    }

    protected function resolvePaymentType(array $paymentData): ?PaymentType
    {
        if (array_key_exists('type', $paymentData)) {
            return $this->normalizePaymentType($paymentData['type']);
        }

        $defaultAmount = $this->resolveDefaultAmount(PaymentType::DownPayment);

        return $defaultAmount > 0 ? PaymentType::DownPayment : null;
    }

    protected function resolveAmount(Reservation $reservation, PaymentType $paymentType, mixed $amount): float
    {
        if ($amount !== null) {
            return (float) $amount;
        }

        $defaultAmount = $this->resolveDefaultAmount($paymentType);

        if ($defaultAmount > 0) {
            return $defaultAmount;
        }

        return (float) $reservation->amount_due;
    }

    protected function resolvePaymentMethod(PaymentMethod|string $method): PaymentMethod
    {
        return $method instanceof PaymentMethod
            ? $method
            : PaymentMethod::from($method);
    }

    protected function resolvePaymentStatus(PaymentStatus|string $status): PaymentStatus
    {
        return $status instanceof PaymentStatus
            ? $status
            : PaymentStatus::from($status);
    }

    protected function normalizePaymentType(PaymentType|string|null $paymentType): ?PaymentType
    {
        if ($paymentType === null || $paymentType === '') {
            return null;
        }

        return $paymentType instanceof PaymentType
            ? $paymentType
            : PaymentType::from($paymentType);
    }

    protected function resolvePaidAt(PaymentStatus $status, mixed $paidAt): ?CarbonInterface
    {
        if (! in_array($status, [PaymentStatus::AwaitingVerification, PaymentStatus::Paid, PaymentStatus::Refunded], true)) {
            return null;
        }

        return $this->resolveDateTime($paidAt ?? now());
    }

    protected function resolveDateTime(mixed $value): ?CarbonInterface
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value;
        }

        return Carbon::parse($value);
    }

    protected function generatePaymentCode(): string
    {
        do {
            $code = 'PAY-'.Str::upper(Str::random(8));
        } while (Payment::query()->where('payment_code', $code)->exists());

        return $code;
    }
}
