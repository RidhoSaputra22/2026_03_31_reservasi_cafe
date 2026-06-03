<?php

namespace App\Enums;

enum GuestPaymentPlan: string
{
    case FullPayment = 'full_payment';
    case DownPayment25 = 'down_payment_25';
    case DownPayment50 = 'down_payment_50';

    public function label(): string
    {
        return match ($this) {
            self::FullPayment => 'Bayar Lunas',
            self::DownPayment25 => 'DP 25%',
            self::DownPayment50 => 'DP 50%',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::FullPayment => 'Pembayaran penuh di awal reservasi.',
            self::DownPayment25 => 'Booking aman dengan DP seperempat harga paket.',
            self::DownPayment50 => 'Cocok untuk komitmen kunjungan yang lebih pasti.',
        };
    }

    public function paymentType(): PaymentType
    {
        return match ($this) {
            self::FullPayment => PaymentType::FullPayment,
            self::DownPayment25, self::DownPayment50 => PaymentType::DownPayment,
        };
    }

    public function amount(int $packagePriceAmount, float $fallbackDownPaymentAmount = 0): int
    {
        $fallbackAmount = max(0, (int) round($fallbackDownPaymentAmount));

        if ($packagePriceAmount <= 0) {
            return $this === self::FullPayment ? $fallbackAmount : $fallbackAmount;
        }

        return match ($this) {
            self::FullPayment => $packagePriceAmount,
            self::DownPayment25 => (int) round($packagePriceAmount * 0.25),
            self::DownPayment50 => (int) round($packagePriceAmount * 0.50),
        };
    }
}
