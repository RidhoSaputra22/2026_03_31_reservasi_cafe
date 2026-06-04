@php
    $midtransClientKey = config('services.midtrans.client_key');
    $midtransSnapJsUrl = config('services.midtrans.is_production', false)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
    $sessionPaymentSnapToken = session('payment_snap_token');
    $sessionPaymentOrderId = session('payment_order_id');
    $hasSnapPayment = filled($sessionPaymentSnapToken)
        || ($nextReservation?->status === \App\Enums\ReservationStatus::PendingPayment
            && $nextReservation?->latestPayment?->status === \App\Enums\PaymentStatus::Pending
            && filled($nextReservation?->latestPayment?->snap_token))
        || $reservations->contains(fn ($reservation) => $reservation->status === \App\Enums\ReservationStatus::PendingPayment
            && $reservation->latestPayment?->status === \App\Enums\PaymentStatus::Pending
            && filled($reservation->latestPayment?->snap_token));
    $hasPendingExpiryCountdown = $reservations->contains(
        fn ($reservation) => $reservation->status === \App\Enums\ReservationStatus::PendingPayment
            && $reservation->latestPayment?->status === \App\Enums\PaymentStatus::Pending
            && $reservation->latestPayment?->pendingExpiresAt() !== null,
    );
    $hasPendingExpiryCountdown = $hasPendingExpiryCountdown
        || ($nextReservation?->status === \App\Enums\ReservationStatus::PendingPayment
            && $nextReservation?->latestPayment?->status === \App\Enums\PaymentStatus::Pending
            && $nextReservation?->latestPayment?->pendingExpiresAt() !== null);
@endphp

<x-layouts.app>
    <div>
        @include('guest.components.site-navbar')

        <section class="mx-auto min-h-screen w-full max-w-7xl space-y-10 px-6 py-20 md:px-12">
            <div class="space-y-3">
                <p class="text-sm uppercase tracking-[0.3em] text-primary/60">Akun Pelanggan</p>
                <h1 class="text-4xl font-semibold text-primary">Halo, {{ $user->name }}.</h1>
                <p class="max-w-3xl text-sm font-light text-gray-600">
                    Di sini kamu bisa melihat status reservasi, melanjutkan pembayaran jika tersedia, dan membatalkan
                    reservasi yang belum check-in.
                </p>
            </div>

            @if (filled($sessionPaymentSnapToken))
                <div class="rounded-3xl border border-primary/20 bg-primary/5 p-5">
                    <p class="text-sm font-medium text-primary">
                        Reservasi berhasil dibuat. Jika ingin menyelesaikan pembayaran online sekarang, lanjutkan lewat tombol di bawah.
                    </p>
                    <button type="button" data-midtrans-snap-button data-snap-token="{{ $sessionPaymentSnapToken }}"
                        data-order-id="{{ $sessionPaymentOrderId }}"
                        class="guest-loading-button mt-4 inline-flex rounded-xl bg-primary px-4 py-3 text-sm font-semibold text-white transition hover:bg-primary/90">
                        <span class="guest-loading-button__label">
                            {{ session('payment_redirect_label') ?? 'Lanjutkan Pembayaran' }}
                        </span>
                        <span class="guest-loading-button__state">
                            <span class="guest-loading-button__spinner" aria-hidden="true"></span>
                            <span>Memuat pembayaran...</span>
                        </span>
                    </button>
                </div>
            @endif

            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-light text-gray-500">Total Reservasi</p>
                    <p class="mt-2 text-3xl font-semibold text-primary">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-light text-gray-500">Akan Datang</p>
                    <p class="mt-2 text-3xl font-semibold text-primary">{{ $stats['upcoming'] }}</p>
                </div>
                <div class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-light text-gray-500">Selesai</p>
                    <p class="mt-2 text-3xl font-semibold text-primary">{{ $stats['completed'] }}</p>
                </div>
                <div class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-light text-gray-500">Butuh Tindakan</p>
                    <p class="mt-2 text-3xl font-semibold text-primary">{{ $stats['needs_action'] }}</p>
                </div>
            </div>

            @if ($nextReservation)
                @php
                    $nextPayment = $nextReservation->latestPayment;
                    $canContinueNextPayment = filled($nextPayment?->snap_token)
                        && $nextReservation->status === \App\Enums\ReservationStatus::PendingPayment
                        && $nextPayment?->status === \App\Enums\PaymentStatus::Pending;
                    $nextPaymentExpiresAt = $nextPayment?->pendingExpiresAt();
                @endphp
                <div class="rounded-md border border-primary/15 bg-primary p-6 text-white shadow-xl">
                    <p class="text-sm uppercase tracking-[0.3em] text-white/70">Reservasi Terdekat</p>
                    <div class="mt-4 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                        <div class="space-y-2">
                            <h2 class="text-3xl font-semibold">
                                {{ $nextReservation->package_name ?? 'Reservasi Cafe Amiko' }}
                            </h2>
                            <p class="text-sm font-light text-white/80">
                                {{ $nextReservation->reservation_code }} •
                                {{ $nextReservation->reservation_date?->translatedFormat('d F Y') }} •
                                {{ substr($nextReservation->start_time, 0, 5) }} - {{ substr((string) $nextReservation->end_time, 0, 5) }}
                            </p>
                            <p class="text-sm font-light text-white/80">
                                {{ $nextReservation->guest_count }} tamu •
                                {{ $nextReservation->cafeTable?->name ?? 'Meja akan ditentukan sistem' }}
                            </p>
                            @if ($canContinueNextPayment && $nextPaymentExpiresAt)
                                <p class="text-sm font-medium text-amber-100">
                                    Bayar sebelum {{ $nextPaymentExpiresAt->translatedFormat('d M Y H:i') }}.
                                </p>
                            @endif
                        </div>
                        @if ($canContinueNextPayment)
                            <button type="button" data-midtrans-snap-button data-snap-token="{{ $nextPayment->snap_token }}"
                                data-order-id="{{ $nextPayment->midtrans_order_id ?: $nextPayment->transaction_reference }}"
                                class="guest-loading-button inline-flex rounded-xl bg-white px-4 py-3 text-sm font-semibold text-primary transition hover:bg-white/90">
                                <span class="guest-loading-button__label">Lanjut Pembayaran</span>
                                <span class="guest-loading-button__state">
                                    <span class="guest-loading-button__spinner" aria-hidden="true"></span>
                                    <span>Memuat pembayaran...</span>
                                </span>
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            <div class="space-y-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-semibold text-primary">Riwayat Reservasi</h2>
                        <p class="text-sm font-light text-gray-500">Semua reservasi pelanggan yang terhubung dengan akun ini.</p>
                    </div>
                    <a href="{{ route('packages.index') }}"
                        class="inline-flex rounded-xl border border-primary/20 px-4 py-3 text-sm font-semibold text-primary transition hover:border-primary hover:bg-primary/5">
                        Buat Reservasi Baru
                    </a>
                </div>

                @if ($reservations->isEmpty())
                    <div class="rounded-3xl border border-dashed border-gray-200 bg-white p-10 text-center">
                        <h3 class="text-xl font-semibold text-primary">Belum ada reservasi.</h3>
                        <p class="mt-3 text-sm font-light text-gray-500">Mulai dari halaman paket untuk memilih slot yang tersedia.</p>
                    </div>
                @else
                    <div class="grid gap-5">
                        @foreach ($reservations as $reservation)
                            @php
                                $payment = $reservation->latestPayment;
                                $isHighlighted = (int) session('highlight_reservation_id') === $reservation->id;
                                $canCancel = in_array($reservation->status->value, [
                                    \App\Enums\ReservationStatus::PendingPayment->value,
                                    \App\Enums\ReservationStatus::AwaitingConfirmation->value,
                                    \App\Enums\ReservationStatus::Confirmed->value,
                                ], true);
                                $canContinuePayment = filled($payment?->snap_token)
                                    && $reservation->status === \App\Enums\ReservationStatus::PendingPayment
                                    && $payment?->status === \App\Enums\PaymentStatus::Pending;
                                $paymentExpiresAt = $payment?->pendingExpiresAt();
                            @endphp
                            <article id="reservation-{{ $reservation->id }}"
                                class="rounded-md border bg-white p-6 shadow-sm {{ $isHighlighted ? 'border-primary shadow-primary/10' : 'border-gray-100' }}">
                                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="space-y-3">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <h3 class="text-2xl font-semibold text-primary">
                                                {{ $reservation->package_name ?? 'Reservasi Cafe Amiko' }}
                                            </h3>
                                            <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                                                {{ $reservation->status->label() }}
                                            </span>
                                        </div>
                                        <p class="text-sm font-light text-gray-500">
                                            {{ $reservation->reservation_code }} •
                                            {{ $reservation->reservation_date?->translatedFormat('d F Y') }} •
                                            {{ substr($reservation->start_time, 0, 5) }} - {{ substr((string) $reservation->end_time, 0, 5) }}
                                        </p>

                                        <dl class="grid gap-3 text-sm text-gray-600 md:grid-cols-2 xl:grid-cols-4">
                                            <div>
                                                <dt class="font-semibold text-primary">Tamu</dt>
                                                <dd>{{ $reservation->guest_count }} orang</dd>
                                            </div>
                                            <div>
                                                <dt class="font-semibold text-primary">Meja</dt>
                                                <dd>{{ $reservation->cafeTable?->name ?? 'Belum ditentukan' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="font-semibold text-primary">Pembayaran</dt>
                                                <dd>{{ $payment?->status?->label() ?? 'Tidak ada pembayaran' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="font-semibold text-primary">Nominal</dt>
                                                <dd>Rp{{ number_format((float) $reservation->amount_due, 0, ',', '.') }}</dd>
                                            </div>

                                        </dl>

                                        @if ($reservation->notes)
                                            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4 text-sm font-light text-gray-600">
                                                {!! nl2br(e($reservation->notes)) !!}
                                            </div>
                                        @endif

                                        @if ($reservation->status === \App\Enums\ReservationStatus::PendingPayment && $paymentExpiresAt)
                                            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                                                <p class="font-semibold">
                                                    Selesaikan pembayaran sebelum {{ $paymentExpiresAt->translatedFormat('d M Y H:i') }}.
                                                </p>
                                                <p class="mt-1" data-payment-expiry-countdown
                                                    data-expiry-at="{{ $paymentExpiresAt->toIso8601String() }}">
                                                    Sisa waktu pembayaran sedang dihitung...
                                                </p>
                                            </div>
                                        @endif

                                        @if ($reservation->status === \App\Enums\ReservationStatus::Cancelled && filled($reservation->cancellation_reason))
                                            <div class="rounded-2xl border border-red-100 bg-red-50 p-4 text-sm text-red-700">
                                                {{ $reservation->cancellation_reason }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex flex-col gap-3 lg:w-56">
                                        @if ($canContinuePayment)
                                            <button type="button" data-midtrans-snap-button data-snap-token="{{ $payment->snap_token }}"
                                                data-order-id="{{ $payment->midtrans_order_id ?: $payment->transaction_reference }}"
                                                class="guest-loading-button inline-flex justify-center rounded-xl bg-primary px-4 py-3 text-sm font-semibold text-white transition hover:bg-primary/90">
                                                <span class="guest-loading-button__label">Lanjut Pembayaran</span>
                                                <span class="guest-loading-button__state">
                                                    <span class="guest-loading-button__spinner" aria-hidden="true"></span>
                                                    <span>Memuat pembayaran...</span>
                                                </span>
                                            </button>
                                        @endif

                                        <a href="{{ route('booking.show', ['slug' => $reservation->package_slug ?? 'coffee-date-corner']) }}"
                                            class="inline-flex justify-center rounded-xl border border-primary/20 px-4 py-3 text-sm font-semibold text-primary transition hover:border-primary hover:bg-primary/5">
                                            Lihat Paket
                                        </a>

                                        @if ($canCancel)
                                            <form method="POST" action="{{ route('customer.reservations.cancel', $reservation) }}" data-loading-form>
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" data-loading-button
                                                    class="guest-loading-button w-full rounded-xl border border-red-200 px-4 py-3 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                                                    <span class="guest-loading-button__label">Batalkan Reservasi</span>
                                                    <span class="guest-loading-button__state">
                                                        <span class="guest-loading-button__spinner" aria-hidden="true"></span>
                                                        <span>Membatalkan...</span>
                                                    </span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        @if ($hasPendingExpiryCountdown)
            @push('scripts')
                <script>
                    window.addEventListener('load', function() {
                        const nodes = Array.from(document.querySelectorAll('[data-payment-expiry-countdown]'));

                        if (!nodes.length) {
                            return;
                        }

                        const formatRemaining = (diffMs) => {
                            const totalSeconds = Math.max(0, Math.floor(diffMs / 1000));
                            const hours = Math.floor(totalSeconds / 3600);
                            const minutes = Math.floor((totalSeconds % 3600) / 60);
                            const seconds = totalSeconds % 60;

                            if (hours > 0) {
                                return `${hours}j ${String(minutes).padStart(2, '0')}m ${String(seconds).padStart(2, '0')}d`;
                            }

                            return `${String(minutes).padStart(2, '0')}m ${String(seconds).padStart(2, '0')}d`;
                        };

                        const updateNode = (node) => {
                            const expiryAt = new Date(node.dataset.expiryAt || '');

                            if (Number.isNaN(expiryAt.getTime())) {
                                return false;
                            }

                            const remaining = expiryAt.getTime() - Date.now();

                            if (remaining <= 0) {
                                node.textContent = 'Batas pembayaran telah habis. Reservasi ini akan dibatalkan otomatis.';

                                return false;
                            }

                            node.textContent = `Sisa waktu pembayaran: ${formatRemaining(remaining)}`;

                            return true;
                        };

                        const tick = () => {
                            let hasActiveCountdown = false;

                            nodes.forEach((node) => {
                                hasActiveCountdown = updateNode(node) || hasActiveCountdown;
                            });

                            if (!hasActiveCountdown) {
                                window.clearInterval(intervalId);
                            }
                        };

                        let intervalId = null;

                        tick();
                        intervalId = window.setInterval(tick, 1000);
                    });
                </script>
            @endpush
        @endif

        @if ($hasSnapPayment && filled($midtransClientKey))
            @push('scripts')
                <script src="{{ $midtransSnapJsUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
                <script>
                    window.addEventListener('load', function() {
                        const profileUrl = @js(route('customer.profile'));

                        if (!window.snap) {
                            return;
                        }

                        const buildProfileUrl = (orderId) => {
                            const url = new URL(profileUrl, window.location.origin);

                            if (orderId) {
                                url.searchParams.set('midtrans_order_id', orderId);
                            }

                            return url.toString();
                        };

                        const openSnapPayment = (button, token, fallbackOrderId) => {
                            if (!token) {
                                return;
                            }

                            if (button && window.appCafe?.setButtonLoadingState) {
                                window.appCafe.setButtonLoadingState(button, true);
                            }

                            window.snap.pay(token, {
                                onSuccess: function(result) {
                                    window.location.href = buildProfileUrl(result?.order_id || fallbackOrderId);
                                },
                                onPending: function() {
                                    if (button && window.appCafe?.setButtonLoadingState) {
                                        window.appCafe.setButtonLoadingState(button, false);
                                    }
                                },
                                onError: function() {
                                    if (button && window.appCafe?.setButtonLoadingState) {
                                        window.appCafe.setButtonLoadingState(button, false);
                                    }
                                },
                                onClose: function() {
                                    if (button && window.appCafe?.setButtonLoadingState) {
                                        window.appCafe.setButtonLoadingState(button, false);
                                    }
                                },
                            });
                        };

                        document.querySelectorAll('[data-midtrans-snap-button]').forEach(function(button) {
                            button.addEventListener('click', function() {
                                openSnapPayment(button, button.dataset.snapToken, button.dataset.orderId);
                            });
                        });
                    });
                </script>
            @endpush
        @endif

        @include('guest.components.site-footer')
    </div>
</x-layouts.app>
