<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cafe_profile_id',
        'name',
        'category',
        'description',
        'price',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'bool',
        ];
    }

    public function cafeProfile(): BelongsTo
    {
        return $this->belongsTo(CafeProfile::class);
    }
}
