<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case Qris = 'qris';
    case Card = 'card';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Tunai',
            self::BankTransfer => 'Transfer Bank',
            self::Qris => 'QRIS',
            self::Card => 'Kartu',
        };
    }
}
