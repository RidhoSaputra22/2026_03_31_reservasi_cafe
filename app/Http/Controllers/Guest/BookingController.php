<?php

namespace App\Http\Controllers\Guest;

use App\Enums\GuestPaymentPlan;
use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guest\StoreGuestReservationRequest;
use App\Http\Requests\Guest\StoreGuestReviewRequest;
use App\Models\CafeProfile;
use App\Models\CafeTable;
use App\Models\GuestReview;
use App\Models\ReservationSlot;
use App\Services\CafePackageCatalog;
use App\Services\CafeReservation\CafeAvailabilityService;
use App\Services\CafeReservation\CafeReservationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class BookingController extends Controller
{
    public function __construct(
        private readonly CafePackageCatalog $packageCatalog,
        private readonly CafeAvailabilityService $availabilityService,
        private readonly CafeReservationService $reservationService,
    ) {
    }

    public function show(Request $request, string $slug): View|RedirectResponse
    {
        $package = $this->resolvePackage($slug);
        $profile = CafeProfile::query()->first();
        $maxGuestCount = $this->maxGuestCount();
        $selectedDurationHours = $this->normalizeDurationHours(old('duration_hours', $request->integer('duration_hours', 1)));
        $guestCount = max(1, min($maxGuestCount, (int) old('guest_count', $request->integer('guest_count', 2))));

        if ($slug !== $package['slug']) {
            return redirect()->route('booking.show', ['slug' => $package['slug']]);
        }

        $defaultSchedule = $this->nextAvailableSchedule($guestCount, $selectedDurationHours);
        $selectedDate = old('reservation_date', $request->string('date')->toString())
            ?: ($defaultSchedule['date'] ?? now()->toDateString());
        $selectedTime = $this->normalizeStartTimeInput(
            old('start_time', $request->string('time')->toString())
                ?: ($defaultSchedule['time'] ?? null),
        );
        $availability = $this->availabilityPayload($selectedDate, $selectedTime, $guestCount, $selectedDurationHours);
        $estimatedPrice = $this->packageCatalog->calculatePrice($package, $selectedDurationHours);
        $downPaymentAmount = (float) ($profile?->down_payment_amount ?? 0);
        $reviewsQuery = GuestReview::query()
            ->where('package_slug', $package['slug'])
            ->where('is_published', true)
            ->latest();
        $reviewCount = (clone $reviewsQuery)->count();
        $reviewAverage = $reviewCount > 0
            ? round((float) ((clone $reviewsQuery)->avg('rating')), 1)
            : null;

        return view('guest.booking.booking', [
            'profile' => $profile,
            'package' => $package,
            'selectedDate' => $selectedDate,
            'selectedTime' => $selectedTime,
            'selectedDurationHours' => $selectedDurationHours,
            'durationOptions' => $this->durationOptions(),
            'estimatedPrice' => $estimatedPrice,
            'estimatedPriceLabel' => $this->packageCatalog->formatMoney($estimatedPrice),
            'guestCount' => $guestCount,
            'maxGuestCount' => $maxGuestCount,
            'availability' => $availability,
            'paymentMethods' => PaymentMethod::cases(),
            'downPaymentAmount' => $downPaymentAmount,
            'reviews' => $reviewsQuery->limit(8)->get(),
            'reviewCount' => $reviewCount,
            'reviewAverage' => $reviewAverage,
        ]);
    }

    public function availability(Request $request, string $slug): JsonResponse
    {
        $package = $this->resolvePackage($slug);
        $validated = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:'.$this->maxGuestCount()],
            'duration_hours' => ['required', 'integer', 'min:1', 'max:'.$this->maxDurationHours()],
        ]);

        $durationHours = (int) $validated['duration_hours'];

        return response()->json([
            'package_slug' => $package['slug'],
            'estimated_price' => $this->packageCatalog->calculatePrice($package, $durationHours),
            'estimated_price_label' => $this->packageCatalog->formatMoney(
                $this->packageCatalog->calculatePrice($package, $durationHours),
            ),
            ...$this->availabilityPayload(
                $validated['date'],
                $validated['start_time'],
                (int) $validated['guest_count'],
                $durationHours,
            ),
        ]);
    }

    public function store(StoreGuestReservationRequest $request, string $slug): RedirectResponse
    {
        $package = $this->resolvePackage($slug);
        $profile = CafeProfile::query()->first();
        $user = $request->user();
        $validated = $request->validated();
        $durationHours = (int) $validated['duration_hours'];
        $totalPrice = $this->packageCatalog->calculatePrice($package, $durationHours);

        if (! $user?->isCustomer()) {
            throw ValidationException::withMessages([
                'customer_name' => 'Reservasi hanya dapat dibuat menggunakan akun pelanggan.',
            ]);
        }

        $user->forceFill([
            'name' => $validated['customer_name'],
            'phone_number' => $validated['customer_phone'],
        ])->save();

        $paymentPlan = GuestPaymentPlan::tryFrom((string) ($validated['payment_plan'] ?? ''));
        $paymentPayload = [
            'method' => $validated['payment_method'],
        ];

        if ($paymentPlan instanceof GuestPaymentPlan) {
            $paymentPayload = [
                ...$paymentPayload,
                'type' => $paymentPlan->paymentType()->value,
                'amount' => $paymentPlan->amount(
                    $totalPrice,
                    (float) ($profile?->down_payment_amount ?? 0),
                ),
                'notes' => 'Skema pembayaran tamu: '.$paymentPlan->label(),
            ];
        } else {
            $paymentPayload['type'] = PaymentType::DownPayment->value;
        }

        try {
            $result = $this->reservationService->createReservation([
                'user_id' => $user->id,
                'reservation_package_id' => $package['id'] ?? null,
                'package_slug' => $package['slug'],
                'package_name' => $package['name'],
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'reservation_date' => $validated['reservation_date'],
                'start_time' => $validated['start_time'],
                'duration_hours' => $durationHours,
                'guest_count' => (int) $validated['guest_count'],
                'total_price' => $totalPrice,
                'notes' => $this->buildReservationNotes(
                    $package['name'],
                    $durationHours,
                    $totalPrice,
                    $validated['notes'] ?? null,
                ),
                'payment' => $paymentPayload,
            ]);
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'reservation_date' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Guest reservation creation failed.', [
                'package_slug' => $package['slug'],
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'reservation_date' => 'Reservasi belum berhasil dibuat. Coba lagi beberapa saat lagi.',
            ]);
        }

        $reservation = $result['reservation'];
        $payment = $result['payment'];

        if ($request->boolean('start_midtrans_payment') && filled($payment?->snap_token)) {
            return redirect()
                ->route('booking.show', [
                    'slug' => $package['slug'],
                    'date' => $validated['reservation_date'],
                    'time' => $validated['start_time'],
                    'duration_hours' => $durationHours,
                    'guest_count' => (int) $validated['guest_count'],
                ])
                ->with('success', 'Reservasi berhasil dibuat dengan kode '.$reservation->reservation_code.'.')
                ->with('highlight_reservation_id', $reservation->id)
                ->with('payment_snap_token', $payment->snap_token)
                ->with('payment_order_id', $payment->midtrans_order_id ?: $payment->transaction_reference);
        }

        return redirect()
            ->route('customer.profile')
            ->with('success', 'Reservasi berhasil dibuat dengan kode '.$reservation->reservation_code.'.')
            ->with('highlight_reservation_id', $reservation->id)
            ->with('payment_snap_token', $payment?->snap_token)
            ->with('payment_order_id', $payment?->midtrans_order_id ?: $payment?->transaction_reference)
            ->with('payment_redirect_label', $payment?->snap_token ? 'Lanjutkan pembayaran online' : null);
    }

    public function storeReview(StoreGuestReviewRequest $request, string $slug): RedirectResponse
    {
        $package = $this->resolvePackage($slug);
        $validated = $request->validated();

        GuestReview::query()->create([
            'user_id' => $request->user()?->id,
            'package_slug' => $package['slug'],
            'guest_name' => $request->user()?->name ?? trim((string) $validated['guest_name']),
            'rating' => (int) $validated['rating'],
            'comment' => trim((string) $validated['comment']),
            'is_published' => true,
        ]);

        return redirect(route('booking.show', ['slug' => $package['slug']]).'#reviews')
            ->with('success', 'Ulasan berhasil dikirim.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolvePackage(string $slug): array
    {
        $package = $this->packageCatalog->find($slug);

        abort_unless($package !== null, 404);

        return $package;
    }

    /**
     * @return array{date: string, time: string}|null
     */
    protected function nextAvailableSchedule(int $guestCount, int $durationHours): ?array
    {
        for ($offset = 0; $offset <= 21; $offset++) {
            $date = now()->addDays($offset)->startOfDay();
            $activeSlots = $this->availabilityService->activeSlotsForDate($date);

            foreach ($activeSlots as $slot) {
                $availability = $this->availabilityService->checkAvailability(
                    $date,
                    $slot->start_time,
                    $guestCount,
                    null,
                    $durationHours,
                );

                if ($availability['is_available'] === true) {
                    return [
                        'date' => $date->toDateString(),
                        'time' => Str::substr($slot->start_time, 0, 5),
                    ];
                }
            }
        }

        return null;
    }

    /**
     * @return array{
     *     date: string,
     *     date_label: string,
     *     start_time: string|null,
     *     end_time: string|null,
     *     duration_hours: int,
     *     guest_count: int,
     *     is_available: bool,
     *     message: string|null,
     *     available_tables_count: int,
     *     available_label: string|null,
     *     recommended_table: string|null,
     *     operational_label: string|null,
     *     active_windows: array<int, string>
     * }
     */
    protected function availabilityPayload(
        string $date,
        ?string $startTime,
        int $guestCount,
        int $durationHours,
    ): array {
        $selectedDate = Carbon::parse($date)->startOfDay();
        $resolvedStartTime = $this->normalizeStartTimeInput($startTime);
        $activeWindows = $this->availabilityService
            ->activeSlotsForDate($selectedDate)
            ->map(
                fn (ReservationSlot $slot): string => ($slot->name ? $slot->name.' · ' : '')
                    .Str::substr($slot->start_time, 0, 5)
                    .' - '
                    .Str::substr((string) $slot->end_time, 0, 5),
            )
            ->values()
            ->all();

        if ($resolvedStartTime === null) {
            return [
                'date' => $selectedDate->toDateString(),
                'date_label' => $selectedDate->translatedFormat('l, d F Y'),
                'start_time' => null,
                'end_time' => null,
                'duration_hours' => $durationHours,
                'guest_count' => $guestCount,
                'is_available' => false,
                'message' => 'Isi jam mulai reservasi untuk mengecek ketersediaan meja.',
                'available_tables_count' => 0,
                'available_label' => null,
                'recommended_table' => null,
                'operational_label' => $activeWindows !== []
                    ? 'Rentang aktif: '.implode(', ', $activeWindows)
                    : 'Belum ada rentang jam reservasi aktif pada hari ini.',
                'active_windows' => $activeWindows,
            ];
        }

        $availability = $this->availabilityService->checkAvailability(
            $selectedDate,
            $resolvedStartTime,
            $guestCount,
            null,
            $durationHours,
        );
        $availableCount = $availability['available_tables']->count();
        $slot = $availability['slot'];

        return [
            'date' => $selectedDate->toDateString(),
            'date_label' => $selectedDate->translatedFormat('l, d F Y'),
            'start_time' => Str::substr($availability['start_time'], 0, 5),
            'end_time' => $availability['end_time'] !== null
                ? Str::substr((string) $availability['end_time'], 0, 5)
                : null,
            'duration_hours' => $durationHours,
            'guest_count' => $guestCount,
            'is_available' => $availability['is_available'],
            'message' => $availability['is_available']
                ? null
                : ($availability['reason'] ?? 'Jadwal yang dipilih belum tersedia.'),
            'available_tables_count' => $availableCount,
            'available_label' => $availability['is_available']
                ? $availableCount.' meja tersedia'
                : ($availability['reason'] ?? 'Tidak tersedia'),
            'recommended_table' => optional($availability['available_tables']->first())->name,
            'operational_label' => $slot instanceof ReservationSlot
                ? 'Masuk rentang aktif: '
                    .($slot->name ? $slot->name.' · ' : '')
                    .Str::substr($slot->start_time, 0, 5)
                    .' - '
                    .Str::substr((string) $slot->end_time, 0, 5)
                : ($activeWindows !== []
                    ? 'Rentang aktif: '.implode(', ', $activeWindows)
                    : 'Belum ada rentang jam reservasi aktif pada hari ini.'),
            'active_windows' => $activeWindows,
        ];
    }

    protected function buildReservationNotes(
        string $packageName,
        int $durationHours,
        int $totalPrice,
        ?string $notes,
    ): string {
        $lines = [
            'Paket reservasi: '.$packageName,
            'Durasi: '.$this->durationLabel($durationHours),
            'Estimasi total: '.$this->packageCatalog->formatMoney($totalPrice),
        ];
        $trimmedNotes = trim((string) $notes);

        if ($trimmedNotes !== '') {
            $lines[] = 'Catatan pelanggan: '.$trimmedNotes;
        }

        return implode(PHP_EOL.PHP_EOL, $lines);
    }

    protected function maxGuestCount(): int
    {
        return max(1, (int) CafeTable::query()->where('is_active', true)->max('capacity'));
    }

    /**
     * @return array<int, int>
     */
    protected function durationOptions(): array
    {
        return range(1, $this->maxDurationHours());
    }

    protected function maxDurationHours(): int
    {
        $maxDuration = ReservationSlot::query()
            ->where('is_active', true)
            ->get()
            ->map(fn (ReservationSlot $slot): int => $this->maxDurationHoursForSlot($slot))
            ->max();

        return max(1, (int) ($maxDuration ?? 1));
    }

    protected function maxDurationHoursForSlot(ReservationSlot $slot): int
    {
        $minutes = Carbon::parse($slot->start_time)
            ->diffInMinutes(Carbon::parse((string) $slot->end_time), false);

        if ($minutes < 60) {
            return 0;
        }

        return (int) floor($minutes / 60);
    }

    protected function normalizeDurationHours(mixed $durationHours): int
    {
        return max(1, min($this->maxDurationHours(), (int) $durationHours));
    }

    protected function normalizeStartTimeInput(?string $startTime): ?string
    {
        $value = trim((string) $startTime);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (Throwable) {
            return null;
        }
    }

    protected function durationLabel(int $durationHours): string
    {
        return $durationHours === 1
            ? '1 jam'
            : $durationHours.' jam';
    }
}
