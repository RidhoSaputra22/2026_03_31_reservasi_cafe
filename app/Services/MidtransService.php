<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Carbon\CarbonInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MidtransService
{
    public function isConfigured(): bool
    {
        return filled($this->serverKey());
    }

    public function makeOrderId(string $reference): string
    {
        $orderId = preg_replace('/[^A-Za-z0-9\-_~.]/', '-', $reference) ?: (string) Str::uuid();

        return Str::limit($orderId, 50, '');
    }

    /**
     * @return array<string, mixed>
     */
    public function createSnapTransaction(Payment $payment, array $options = []): array
    {
        $this->ensureConfigured();

        $response = $this->http()->post(
            $this->snapEndpoint(),
            $this->buildSnapPayload($payment, $options),
        );

        if ($response->failed()) {
            throw new RuntimeException('Gagal membuat transaksi Midtrans: '.$this->errorMessage($response->json(), $response->body()));
        }

        return $response->json() ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getTransactionStatus(string $orderId): array
    {
        $this->ensureConfigured();

        $response = $this->http()->get($this->coreApiEndpoint('/v2/'.rawurlencode($orderId).'/status'));

        if ($response->failed()) {
            throw new RuntimeException('Gagal mengambil status transaksi Midtrans: '.$this->errorMessage($response->json(), $response->body()));
        }

        return $response->json() ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function cancelTransaction(string $orderId): array
    {
        $this->ensureConfigured();

        $response = $this->http()->post($this->coreApiEndpoint('/v2/'.rawurlencode($orderId).'/cancel'));

        if ($response->failed()) {
            throw new RuntimeException('Gagal membatalkan transaksi Midtrans: '.$this->errorMessage($response->json(), $response->body()));
        }

        return $response->json() ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function expireTransaction(string $orderId): array
    {
        $this->ensureConfigured();

        $response = $this->http()->post($this->coreApiEndpoint('/v2/'.rawurlencode($orderId).'/expire'));

        if ($response->failed()) {
            throw new RuntimeException('Gagal mengakhiri transaksi Midtrans: '.$this->errorMessage($response->json(), $response->body()));
        }

        return $response->json() ?? [];
    }

    public function mapTransactionStatus(array $payload): PaymentStatus
    {
        $transactionStatus = (string) Arr::get($payload, 'transaction_status', '');
        $fraudStatus = (string) Arr::get($payload, 'fraud_status', '');

        return match ($transactionStatus) {
            'capture' => $fraudStatus === 'challenge'
                ? PaymentStatus::AwaitingVerification
                : PaymentStatus::Paid,
            'settlement' => PaymentStatus::Paid,
            'pending' => PaymentStatus::Pending,
            'refund', 'partial_refund' => PaymentStatus::Refunded,
            'deny', 'cancel', 'expire', 'failure' => PaymentStatus::Failed,
            default => PaymentStatus::Pending,
        };
    }

    public function isSignatureValid(array $payload): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $signatureKey = (string) Arr::get($payload, 'signature_key', '');

        if ($signatureKey === '') {
            return false;
        }

        $expectedSignature = hash('sha512', implode('', [
            Arr::get($payload, 'order_id'),
            Arr::get($payload, 'status_code'),
            Arr::get($payload, 'gross_amount'),
            $this->serverKey(),
        ]));

        return hash_equals($expectedSignature, $signatureKey);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildSnapPayload(Payment $payment, array $options = []): array
    {
        $payment->loadMissing(['reservation.user']);

        $reservation = $payment->reservation;
        $user = $reservation?->user;
        $grossAmount = (int) round((float) $payment->amount);
        [$firstName, $lastName] = $this->splitCustomerName(
            (string) ($reservation?->customer_name ?: $user?->name ?: 'Pelanggan'),
        );

        $payload = [
            'transaction_details' => [
                'order_id' => $payment->transaction_reference ?: $payment->payment_code,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => $this->filledArray([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $user?->email,
                'phone' => $reservation?->customer_phone ?: $user?->phone_number,
            ]),
            'item_details' => [
                [
                    'id' => $payment->payment_code,
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => Str::limit(
                        ($payment->type?->label() ?? 'Pembayaran').' '.($reservation?->reservation_code ?? ''),
                        50,
                        '',
                    ),
                ],
            ],
            'credit_card' => [
                'secure' => (bool) config('services.midtrans.is_3ds', true),
            ],
        ];

        $enabledPayments = $this->enabledPayments();
        $callbacks = $this->callbacks();

        if ($enabledPayments !== []) {
            $payload['enabled_payments'] = $enabledPayments;
        }

        if ($callbacks !== []) {
            $payload['callbacks'] = $callbacks;
        }

        if ($paymentExpiry = $this->paymentExpiryPayload($payment)) {
            $payload['expiry'] = $paymentExpiry;
            $payload['page_expiry'] = [
                'duration' => $paymentExpiry['duration'],
                'unit' => $paymentExpiry['unit'],
            ];
        }

        return array_replace_recursive($payload, Arr::get($options, 'payload', []));
    }

    protected function ensureConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Konfigurasi Midtrans belum lengkap. Isi MIDTRANS_SERVER_KEY terlebih dahulu.');
        }
    }

    protected function http(): PendingRequest
    {
        return Http::asJson()
            ->acceptJson()
            ->withBasicAuth((string) $this->serverKey(), '')
            ->timeout((int) config('services.midtrans.timeout', 15));
    }

    protected function snapEndpoint(): string
    {
        return $this->snapBaseUrl().'/snap/v1/transactions';
    }

    protected function coreApiEndpoint(string $path): string
    {
        return $this->coreApiBaseUrl().'/'.ltrim($path, '/');
    }

    protected function snapBaseUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';
    }

    protected function coreApiBaseUrl(): string
    {
        return $this->isProduction()
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }

    protected function isProduction(): bool
    {
        return (bool) config('services.midtrans.is_production', false);
    }

    protected function serverKey(): ?string
    {
        return config('services.midtrans.server_key');
    }

    /**
     * @return array<int, string>
     */
    protected function enabledPayments(): array
    {
        $enabledPayments = config('services.midtrans.enabled_payments', []);

        if (is_string($enabledPayments)) {
            $enabledPayments = explode(',', $enabledPayments);
        }

        if (! is_array($enabledPayments)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $payment): string => trim((string) $payment),
            $enabledPayments,
        )));
    }

    /**
     * @return array<string, string>
     */
    protected function callbacks(): array
    {
        return $this->filledArray([
            'finish' => config('services.midtrans.finish_url'),
            'unfinish' => config('services.midtrans.unfinish_url'),
            'error' => config('services.midtrans.error_url'),
        ]);
    }

    /**
     * @return array{start_time: string, duration: int, unit: string}|array{}
     */
    protected function paymentExpiryPayload(Payment $payment): array
    {
        $duration = max(1, (int) config('reservations.pending_payment_timeout_minutes', 60));

        $startTime = ($payment->created_at instanceof CarbonInterface ? $payment->created_at->copy() : now())
            ->setTimezone('Asia/Jakarta')
            ->format('Y-m-d H:i:s O');

        return [
            'start_time' => $startTime,
            'duration' => $duration,
            'unit' => 'minute',
        ];
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    protected function splitCustomerName(string $name): array
    {
        $parts = explode(' ', trim($name), 2);

        return [$parts[0] !== '' ? $parts[0] : 'Pelanggan', $parts[1] ?? null];
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    protected function filledArray(array $values): array
    {
        return array_filter($values, fn (mixed $value): bool => filled($value));
    }

    protected function errorMessage(mixed $json, string $body): string
    {
        if (is_array($json)) {
            $message = Arr::get($json, 'error_messages.0')
                ?? Arr::get($json, 'status_message')
                ?? Arr::get($json, 'message');

            if (filled($message)) {
                return (string) $message;
            }
        }

        return $body !== '' ? $body : 'Respons Midtrans tidak valid.';
    }
}
