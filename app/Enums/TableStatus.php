<?php

namespace App\Enums;

enum TableStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Occupied = 'occupied';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Tersedia',
            self::Reserved => 'Dipesan',
            self::Occupied => 'Terisi',
            self::Completed => 'Selesai Digunakan',
        };
    }
}
