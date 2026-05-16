<?php

namespace App\Enums;

enum PaymentType: string
{
    case DownPayment = 'down_payment';
    case FullPayment = 'full_payment';

    public function label(): string
    {
        return match ($this) {
            self::DownPayment => 'Down Payment',
            self::FullPayment => 'Pelunasan',
        };
    }
}
