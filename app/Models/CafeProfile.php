<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CafeProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'address',
        'phone_number',
        'opening_time',
        'closing_time',
        'facilities',
        'reservation_rules',
        'down_payment_amount',
    ];

    protected function casts(): array
    {
        return [
            'facilities' => 'array',
            'down_payment_amount' => 'decimal:2',
        ];
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }
}
