<?php

namespace App\Http\Controllers\Customer;

use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\CafeProfile;
use App\Models\Reservation;
use App\Services\CafeReservation\CafePaymentService;
use App\Services\CafeReservation\CafeReservationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class ProfileController extends Controller
{
    public function show(
        Request $request,
        CafeReservationService $reservationService,
        CafePaymentService $paymentService,
    ): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isAdmin() || $user->isStaff()) {
            return redirect()->route('dashboard');
        }

        $midtransOrderId = trim($request->string('midtrans_order_id')->toString());

        if ($midtransOrderId !== '') {
            try {
                $payment = $paymentService->syncPaymentFromMidtransOrderId($midtransOrderId, $user->id);
            } catch (RuntimeException $exception) {
                return redirect()->route('customer.profile')->with('error', $exception->getMessage());
            }

            [$flashType, $flashMessage] = match ($payment->status) {
                PaymentStatus::Paid => ['success', 'Pembayaran berhasil dikonfirmasi. Reservasimu sudah masuk ke akun profil.'],
                PaymentStatus::AwaitingVerification => ['info', 'Pembayaran sudah diterima dan sedang menunggu verifikasi.'],
                PaymentStatus::Failed => ['error', 'Pembayaran belum berhasil. Silakan coba lagi dari halaman profil.'],
                PaymentStatus::Refunded => ['warning', 'Pembayaran ini telah direfund.'],
                default => ['info', 'Pembayaran masih diproses Midtrans. Kamu bisa melanjutkan atau mengeceknya dari halaman profil.'],
            };

            return redirect()
                ->route('customer.profile')
                ->with($flashType, $flashMessage)
                ->with('highlight_reservation_id', $payment->reservation_id);
        }

        $reservationService->expireTimedOutPendingReservations($user->id);

        $activeStatuses = [
            ReservationStatus::PendingPayment->value,
            ReservationStatus::AwaitingConfirmation->value,
            ReservationStatus::Confirmed->value,
            ReservationStatus::CheckedIn->value,
        ];

        $upcomingQuery = $this->upcomingReservationsQuery($user->reservations(), $activeStatuses);

        $reservations = $user->reservations()
            ->visibleToCustomer()
            ->with(['cafeTable', 'reservationSlot', 'latestPayment'])
            ->orderByDesc('reservation_date')
            ->orderByDesc('start_time')
            ->limit(12)
            ->get();

        $upcomingReservations = (clone $upcomingQuery)
            ->with(['cafeTable', 'reservationSlot', 'latestPayment'])
            ->limit(3)
            ->get();

        $stats = [
            'total' => $user->reservations()->visibleToCustomer()->count(),
            'upcoming' => (clone $upcomingQuery)->count(),
            'completed' => $user->reservations()
                ->visibleToCustomer()
                ->where('status', ReservationStatus::Completed->value)
                ->count(),
            'needs_action' => $user->reservations()
                ->visibleToCustomer()
                ->whereIn('status', [
                    ReservationStatus::PendingPayment->value,
                    ReservationStatus::AwaitingConfirmation->value,
                ])
                ->count(),
        ];

        return view('customer.profile', [
            'profile' => CafeProfile::query()->first(),
            'user' => $user,
            'reservations' => $reservations,
            'upcomingReservations' => $upcomingReservations,
            'nextReservation' => $upcomingReservations->first(),
            'stats' => $stats,
        ]);
    }

    public function cancel(
        Request $request,
        Reservation $reservation,
        CafeReservationService $reservationService,
    ): RedirectResponse {
        abort_unless($reservation->user_id === $request->user()?->id, 404);

        try {
            $reservationService->cancelReservation(
                $reservation,
                'Dibatalkan pelanggan melalui halaman akun.',
                $request->user(),
            );
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Reservasi berhasil dibatalkan.');
    }

    /**
     * @param  HasMany<Reservation, *>  $query
     * @param  array<int, string>  $activeStatuses
     * @return HasMany<Reservation, *>
     */
    private function upcomingReservationsQuery(HasMany $query, array $activeStatuses): HasMany
    {
        $today = now()->toDateString();
        $currentTime = now()->format('H:i:s');

        return $query
            ->visibleToCustomer()
            ->whereIn('status', $activeStatuses)
            ->where(function (Builder $query) use ($currentTime, $today): void {
                $query
                    ->whereDate('reservation_date', '>', $today)
                    ->orWhere(function (Builder $query) use ($currentTime, $today): void {
                        $query
                            ->whereDate('reservation_date', $today)
                            ->where('start_time', '>=', $currentTime);
                    });
            })
            ->orderBy('reservation_date')
            ->orderBy('start_time');
    }
}
