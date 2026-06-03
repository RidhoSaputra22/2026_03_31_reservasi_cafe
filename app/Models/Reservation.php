<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_code',
        'user_id',
        'cafe_table_id',
        'reservation_slot_id',
        'reservation_package_id',
        'package_slug',
        'package_name',
        'customer_name',
        'customer_phone',
        'reservation_date',
        'start_time',
        'end_time',
        'duration_hours',
        'guest_count',
        'notes',
        'amount_due',
        'total_price',
        'status',
        'confirmed_at',
        'checked_in_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
        'confirmed_by',
        'checked_in_by',
        'cancelled_by',
    ];

    protected function casts(): array
    {
        return [
            'reservation_date' => 'date',
            'duration_hours' => 'int',
            'guest_count' => 'int',
            'amount_due' => 'decimal:2',
            'total_price' => 'decimal:2',
            'status' => ReservationStatus::class,
            'confirmed_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cafeTable(): BelongsTo
    {
        return $this->belongsTo(CafeTable::class);
    }

    public function reservationSlot(): BelongsTo
    {
        return $this->belongsTo(ReservationSlot::class);
    }

    public function reservationPackage(): BelongsTo
    {
        return $this->belongsTo(ReservationPackage::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
