<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case AwaitingVerification = 'awaiting_verification';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::AwaitingVerification => 'Menunggu Verifikasi',
            self::Paid => 'Berhasil',
            self::Failed => 'Gagal',
            self::Refunded => 'Refund',
        };
    }
}
