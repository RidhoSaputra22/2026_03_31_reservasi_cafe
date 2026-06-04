<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_code',
        'reservation_id',
        'parent_payment_id',
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

    public function parentPayment(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_payment_id');
    }

    public function childPayments(): HasMany
    {
        return $this->hasMany(self::class, 'parent_payment_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isSettlementPayment(): bool
    {
        return $this->type === PaymentType::FullPayment;
    }

    public function isDownPayment(): bool
    {
        return $this->type === PaymentType::DownPayment;
    }

    public function hasActiveSnapToken(): bool
    {
        return $this->status === PaymentStatus::Pending
            && filled($this->snap_token)
            && ! $this->isPendingExpired();
    }

    public function canBeContinuedByCustomer(): bool
    {
        return $this->isDownPayment() && $this->hasActiveSnapToken();
    }

    public function canBeOpenedInAdmin(): bool
    {
        return $this->isSettlementPayment() && $this->hasActiveSnapToken();
    }

    public function canCreateSettlementFromAdmin(): bool
    {
        if (! $this->isDownPayment() || $this->status !== PaymentStatus::Paid) {
            return false;
        }

        $this->loadMissing('reservation.payments');

        $reservation = $this->reservation;

        if (! $reservation instanceof Reservation) {
            return false;
        }

        return $reservation->latestPaidDownPayment()?->is($this)
            && $reservation->canCreateSettlementPayment();
    }

    public function pendingExpiresAt(): ?CarbonInterface
    {
        if ($this->status !== PaymentStatus::Pending || ! $this->created_at instanceof CarbonInterface) {
            return null;
        }

        return $this->created_at->copy()->addMinutes(
            max(1, (int) config('reservations.pending_payment_timeout_minutes', 60)),
        );
    }

    public function isPendingExpired(?CarbonInterface $referenceTime = null): bool
    {
        $expiresAt = $this->pendingExpiresAt();

        if (! $expiresAt instanceof CarbonInterface) {
            return false;
        }

        $referenceTime = $referenceTime?->copy() ?? now();

        return $expiresAt->lte($referenceTime);
    }
}
