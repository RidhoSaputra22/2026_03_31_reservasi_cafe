<?php

namespace App\Services\CafeReservation;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\ReservationStatus;
use App\Models\CafeProfile;
use App\Models\Payment;
use App\Models\Reservation;
use App\Services\MidtransService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CafePaymentService
{
    public function __construct(
        private readonly MidtransService $midtransService,
    ) {}

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
        $paymentCode = $this->generatePaymentCode();
        $useMidtrans = $this->shouldUseMidtrans($paymentData, $method);
        $transactionReference = $paymentData['transaction_reference'] ?? null;

        if ($useMidtrans) {
            $transactionReference = $this->midtransService->makeOrderId($transactionReference ?: $paymentCode);
        }

        $payment = $reservation->payments()->create([
            'payment_code' => $paymentCode,
            'type' => $paymentType,
            'amount' => $amount,
            'method' => $method,
            'status' => $status,
            'transaction_reference' => $transactionReference,
            'proof_path' => $paymentData['proof_path'] ?? null,
            'paid_at' => $paidAt,
            'verified_at' => $verifiedAt,
            'verified_by' => $paymentData['verified_by'] ?? null,
            'notes' => $paymentData['notes'] ?? null,
        ]);

        if ($useMidtrans) {
            $this->createMidtransTransaction($payment, $paymentData);
        }

        $this->applyReservationPaymentStatus($reservation, $status, $amount);

        return $payment->fresh();
    }

    public function syncFromMidtransNotification(array $payload): Payment
    {
        if (! $this->midtransService->isSignatureValid($payload)) {
            throw new RuntimeException('Signature notifikasi Midtrans tidak valid.');
        }

        $orderId = (string) Arr::get($payload, 'order_id', '');

        if ($orderId === '') {
            throw new RuntimeException('Order ID Midtrans tidak ditemukan pada payload notifikasi.');
        }

        return DB::transaction(function () use ($orderId, $payload): Payment {
            $payment = Payment::query()
                ->where('midtrans_order_id', $orderId)
                ->orWhere('transaction_reference', $orderId)
                ->first();

            if (! $payment instanceof Payment) {
                throw new RuntimeException('Pembayaran Midtrans tidak ditemukan.');
            }

            return $this->syncPaymentFromMidtransPayload($payment, $payload);
        });
    }

    public function syncPaymentFromMidtransOrderId(string $orderId, ?int $userId = null): Payment
    {
        $orderId = trim($orderId);

        if ($orderId === '') {
            throw new RuntimeException('Order ID Midtrans tidak boleh kosong.');
        }

        return DB::transaction(function () use ($orderId, $userId): Payment {
            $payment = Payment::query()
                ->with('reservation')
                ->where('midtrans_order_id', $orderId)
                ->orWhere('transaction_reference', $orderId)
                ->first();

            if (! $payment instanceof Payment) {
                throw new RuntimeException('Pembayaran Midtrans tidak ditemukan.');
            }

            if ($userId !== null && (int) $payment->reservation?->user_id !== $userId) {
                throw new RuntimeException('Pembayaran ini tidak terhubung ke akun pelanggan saat ini.');
            }

            $payload = $this->midtransService->getTransactionStatus($orderId);

            return $this->syncPaymentFromMidtransPayload($payment, $payload);
        });
    }

    protected function createMidtransTransaction(Payment $payment, array $paymentData): void
    {
        $response = $this->midtransService->createSnapTransaction(
            $payment,
            $this->resolveMidtransOptions($paymentData),
        );

        $payment->forceFill([
            'midtrans_order_id' => $payment->transaction_reference,
            'snap_token' => $response['token'] ?? null,
            'snap_redirect_url' => $response['redirect_url'] ?? null,
            'midtrans_status' => $response['transaction_status'] ?? PaymentStatus::Pending->value,
            'midtrans_payload' => $response,
        ])->save();
    }

    protected function syncPaymentFromMidtransPayload(Payment $payment, array $payload): Payment
    {
        $status = $this->midtransService->mapTransactionStatus($payload);
        $paidAt = $this->resolvePaidAt(
            $status,
            Arr::get($payload, 'settlement_time') ?: Arr::get($payload, 'transaction_time'),
        );

        $payment->forceFill([
            'status' => $status,
            'paid_at' => $paidAt,
            'verified_at' => $status === PaymentStatus::Paid ? ($payment->verified_at ?? now()) : null,
            'midtrans_order_id' => $payment->midtrans_order_id ?: Arr::get($payload, 'order_id'),
            'midtrans_status' => Arr::get($payload, 'transaction_status'),
            'midtrans_payload' => $payload,
        ])->save();

        $reservation = $payment->reservation;

        if ($reservation instanceof Reservation) {
            $this->applyReservationPaymentStatus($reservation, $status, (float) $payment->amount);
        }

        return $payment->fresh();
    }

    protected function shouldUseMidtrans(array $paymentData, PaymentMethod $method): bool
    {
        if (array_key_exists('use_midtrans', $paymentData)) {
            return (bool) $paymentData['use_midtrans'];
        }

        if (array_key_exists('midtrans', $paymentData)) {
            return is_array($paymentData['midtrans']) || (bool) $paymentData['midtrans'];
        }

        if (array_key_exists('midtrans_options', $paymentData)) {
            return true;
        }

        if (! $this->midtransService->isConfigured()) {
            return false;
        }

        return in_array($method, [
            PaymentMethod::BankTransfer,
            PaymentMethod::Qris,
            PaymentMethod::Card,
        ], true);
    }

    protected function resolveMidtransOptions(array $paymentData): array
    {
        if (is_array($paymentData['midtrans'] ?? null)) {
            return $paymentData['midtrans'];
        }

        if (is_array($paymentData['midtrans_options'] ?? null)) {
            return $paymentData['midtrans_options'];
        }

        return [];
    }

    protected function applyReservationPaymentStatus(Reservation $reservation, PaymentStatus $status, float $amount): void
    {
        $reservationAttributes = ['amount_due' => $amount];

        if ($status === PaymentStatus::Paid) {
            $reservationAttributes['status'] = ReservationStatus::Confirmed;
            $reservationAttributes['confirmed_at'] = $reservation->confirmed_at ?? now();
        } elseif ($status === PaymentStatus::AwaitingVerification) {
            $reservationAttributes['status'] = ReservationStatus::AwaitingConfirmation;
            $reservationAttributes['confirmed_at'] = null;
        } elseif ($status === PaymentStatus::Refunded) {
            $reservationAttributes['status'] = $reservation->status;
            $reservationAttributes['confirmed_at'] = $reservation->confirmed_at;
        } else {
            $reservationAttributes['status'] = ReservationStatus::PendingPayment;
            $reservationAttributes['confirmed_at'] = null;
        }

        $reservation->forceFill($reservationAttributes)->save();
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
