<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_code',
        'reservation_id',
        'type',
        'amount',
        'method',
        'status',
        'transaction_reference',
        'midtrans_order_id',
        'snap_token',
        'snap_redirect_url',
        'midtrans_status',
        'midtrans_payload',
        'proof_path',
        'paid_at',
        'verified_at',
        'verified_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => PaymentType::class,
            'amount' => 'decimal:2',
            'method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'midtrans_payload' => 'array',
            'paid_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
