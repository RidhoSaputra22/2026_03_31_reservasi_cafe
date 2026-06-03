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

        if ($slug !== $package['slug']) {
            return redirect()->route('booking.show', ['slug' => $package['slug']]);
        }

        $guestCount = max(1, min($maxGuestCount, (int) old('guest_count', $request->integer('guest_count', 2))));
        $selectedDate = old('reservation_date', $request->string('date')->toString()) ?: $this->nextAvailableDate($guestCount);
        $availability = $this->availabilityPayload($selectedDate, $guestCount);
        $selectedTime = old('start_time', $this->firstAvailableTime($availability['slots']));
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
            'guest_count' => ['required', 'integer', 'min:1', 'max:'.$this->maxGuestCount()],
        ]);

        return response()->json([
            'package_slug' => $package['slug'],
            ...$this->availabilityPayload(
                $validated['date'],
                (int) $validated['guest_count'],
            ),
        ]);
    }

    public function store(StoreGuestReservationRequest $request, string $slug): RedirectResponse
    {
        $package = $this->resolvePackage($slug);
        $profile = CafeProfile::query()->first();
        $user = $request->user();
        $validated = $request->validated();

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
                    (int) ($package['price_amount'] ?? 0),
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
                'package_slug' => $package['slug'],
                'package_name' => $package['name'],
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'reservation_date' => $validated['reservation_date'],
                'start_time' => $validated['start_time'],
                'guest_count' => (int) $validated['guest_count'],
                'notes' => $this->buildReservationNotes($package['name'], $validated['notes'] ?? null),
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

        return redirect()
            ->route('customer.profile')
            ->with('success', 'Reservasi berhasil dibuat dengan kode '.$reservation->reservation_code.'.')
            ->with('highlight_reservation_id', $reservation->id)
            ->with('payment_redirect_url', $payment?->snap_redirect_url)
            ->with('payment_redirect_label', $payment?->snap_redirect_url ? 'Lanjutkan pembayaran online' : null);
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

    protected function nextAvailableDate(int $guestCount): string
    {
        for ($offset = 0; $offset <= 21; $offset++) {
            $date = now()->addDays($offset)->toDateString();
            $availability = $this->availabilityPayload($date, $guestCount);

            if ($availability['has_available_slot'] === true) {
                return $date;
            }
        }

        return now()->toDateString();
    }

    /**
     * @return array{
     *     date: string,
     *     date_label: string,
     *     guest_count: int,
     *     has_available_slot: bool,
     *     message: string|null,
     *     slots: array<int, array<string, mixed>>
     * }
     */
    protected function availabilityPayload(string $date, int $guestCount): array
    {
        $selectedDate = Carbon::parse($date)->startOfDay();
        $slots = ReservationSlot::query()
            ->where('day_of_week', $selectedDate->dayOfWeek)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get()
            ->map(function (ReservationSlot $slot) use ($guestCount, $selectedDate): array {
                $availability = $this->availabilityService->checkAvailability(
                    $selectedDate,
                    $slot->start_time,
                    $guestCount,
                );
                $availableCount = $availability['available_tables']->count();

                return [
                    'name' => $slot->name,
                    'time' => Str::substr($slot->start_time, 0, 5),
                    'end_time' => Str::substr((string) $slot->end_time, 0, 5),
                    'label' => Str::substr($slot->start_time, 0, 5).' - '.Str::substr((string) $slot->end_time, 0, 5),
                    'available' => $availability['is_available'],
                    'available_tables_count' => $availableCount,
                    'available_label' => $availability['is_available']
                        ? $availableCount.' meja tersedia'
                        : ($availability['reason'] ?? 'Tidak tersedia'),
                    'recommended_table' => optional($availability['available_tables']->first())->name,
                ];
            })
            ->values();
        $hasAvailableSlot = $slots->contains(fn (array $slot): bool => $slot['available'] === true);

        return [
            'date' => $selectedDate->toDateString(),
            'date_label' => $selectedDate->translatedFormat('l, d F Y'),
            'guest_count' => $guestCount,
            'has_available_slot' => $hasAvailableSlot,
            'message' => $hasAvailableSlot
                ? null
                : 'Belum ada slot tersedia untuk tanggal dan jumlah tamu tersebut.',
            'slots' => $slots->all(),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $slots
     */
    protected function firstAvailableTime(array $slots): ?string
    {
        $firstSlot = collect($slots)
            ->first(fn (array $slot): bool => $slot['available'] === true);

        return is_array($firstSlot) ? ($firstSlot['time'] ?? null) : null;
    }

    protected function buildReservationNotes(string $packageName, ?string $notes): string
    {
        $trimmedNotes = trim((string) $notes);

        if ($trimmedNotes === '') {
            return 'Paket reservasi: '.$packageName;
        }

        return 'Paket reservasi: '.$packageName.PHP_EOL.PHP_EOL.'Catatan pelanggan: '.$trimmedNotes;
    }

    protected function maxGuestCount(): int
    {
        return max(1, (int) CafeTable::query()->where('is_active', true)->max('capacity'));
    }
}
