<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReservationPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'aliases',
        'name',
        'category',
        'image_path',
        'summary',
        'description',
        'base_price',
        'included_hours',
        'extra_hour_price',
        'facilities',
        'notes',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'aliases' => 'array',
            'facilities' => 'array',
            'notes' => 'array',
            'base_price' => 'decimal:2',
            'extra_hour_price' => 'decimal:2',
            'included_hours' => 'int',
            'is_featured' => 'bool',
            'is_active' => 'bool',
            'sort_order' => 'int',
        ];
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
