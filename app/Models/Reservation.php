<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\ReservationStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function paymentRecords(): Collection
    {
        $payments = $this->relationLoaded('payments')
            ? $this->payments
            : $this->payments()->orderBy('created_at')->orderBy('id')->get();

        return $payments
            ->sortBy([
                ['created_at', 'asc'],
                ['id', 'asc'],
            ])
            ->values();
    }

    public function totalPaidAmount(): float
    {
        return (float) $this->paymentRecords()
            ->filter(fn (Payment $payment): bool => $payment->status === PaymentStatus::Paid)
            ->sum(fn (Payment $payment): float => (float) $payment->amount);
    }

    public function remainingAmount(): float
    {
        $totalPrice = (float) $this->total_price;

        if ($totalPrice > 0) {
            return max(0, $totalPrice - $this->totalPaidAmount());
        }

        $latestPayment = $this->paymentRecords()->last();

        if (! $latestPayment instanceof Payment) {
            return 0;
        }

        return $latestPayment->status === PaymentStatus::Paid
            ? 0
            : (float) $latestPayment->amount;
    }

    public function latestPendingPayment(): ?Payment
    {
        return $this->paymentRecords()
            ->reverse()
            ->first(fn (Payment $payment): bool => $payment->status === PaymentStatus::Pending);
    }

    public function activePendingPayment(): ?Payment
    {
        return $this->paymentRecords()
            ->reverse()
            ->first(fn (Payment $payment): bool => $payment->hasActiveSnapToken());
    }

    public function latestSettlementPayment(): ?Payment
    {
        return $this->paymentRecords()
            ->reverse()
            ->first(fn (Payment $payment): bool => $payment->type === PaymentType::FullPayment);
    }

    public function latestOpenSettlementPayment(): ?Payment
    {
        return $this->paymentRecords()
            ->reverse()
            ->first(function (Payment $payment): bool {
                if ($payment->type !== PaymentType::FullPayment) {
                    return false;
                }

                if ($payment->status === PaymentStatus::AwaitingVerification) {
                    return true;
                }

                return $payment->status === PaymentStatus::Pending
                    && ! $payment->isPendingExpired();
            });
    }

    public function latestPaidDownPayment(): ?Payment
    {
        return $this->paymentRecords()
            ->reverse()
            ->first(fn (Payment $payment): bool => $payment->type === PaymentType::DownPayment
                && $payment->status === PaymentStatus::Paid);
    }

    public function canCreateSettlementPayment(): bool
    {
        return $this->totalPaidAmount() > 0
            && $this->remainingAmount() > 0
            && ! ($this->latestOpenSettlementPayment() instanceof Payment);
    }

    public function scopeVisibleToCustomer(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query
                ->where('status', '!=', ReservationStatus::Cancelled->value)
                ->orWhereNull('cancellation_reason')
                ->orWhere('cancellation_reason', '!=', static::expiredPaymentCancellationReason());
        });
    }

    public function pendingPaymentExpiresAt(): ?CarbonInterface
    {
        return $this->latestPayment?->pendingExpiresAt();
    }

    public function wasCancelledBecausePaymentExpired(): bool
    {
        return $this->status === ReservationStatus::Cancelled
            && $this->cancellation_reason === static::expiredPaymentCancellationReason();
    }

    public static function expiredPaymentCancellationReason(): string
    {
        return (string) config(
            'reservations.expired_payment_cancellation_reason',
            'Reservasi dibatalkan otomatis karena batas waktu pembayaran habis.',
        );
    }
}
