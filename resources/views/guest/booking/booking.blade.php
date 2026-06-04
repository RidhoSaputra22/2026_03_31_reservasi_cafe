@php
    $package = $package ?? collect(config('packages'))->first();
    $paymentSnapToken = session('payment_snap_token');
    $paymentOrderId = session('payment_order_id');
    $midtransClientKey = config('services.midtrans.client_key');
    $midtransSnapJsUrl = config('services.midtrans.is_production', false)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
@endphp

<x-layouts.app>
    <div x-data="bookingReservationForm({
        availabilityUrl: @js(route('booking.availability', ['slug' => $package['slug']])),
        initialDate: @js($selectedDate),
        initialTime: @js($selectedTime),
        initialDurationHours: @js($selectedDurationHours),
        durationOptions: @js($durationOptions),
        initialGuestCount: @js($guestCount),
        initialAvailability: @js($availability),
        initialEstimatedPrice: @js($estimatedPrice),
        initialEstimatedPriceLabel: @js($estimatedPriceLabel),
        today: @js(now()->toDateString()),
        maxGuestCount: @js($maxGuestCount),
        initialMidtransLoading: @js(filled($paymentSnapToken) && filled($midtransClientKey)),
    })">
        @include('guest.components.site-navbar')

        <div class="flex min-h-screen flex-col gap-10 p-6 md:p-12 xl:flex-row">
            <div class="space-y-14 xl:flex-[2]">
                <div class="relative overflow-hidden rounded-2xl">
                    <img src="{{ asset($package['image']) }}" class="h-[28rem] w-full object-cover md:h-screen">
                    <span class="absolute inset-0 h-full w-full bg-linear-to-tr from-black to-transparent"></span>
                    <div class="absolute bottom-6 left-6 space-y-5 text-white">
                        <div class="space-y-5">
                            <h1 class="text-5xl font-semibold">{{ $package['name'] }}</h1>
                            <h1 class="text-4xl font-semibold">{{ $package['price'] }}</h1>
                            <p class="text-lg">{{ $package['description'] }}</p>
                            <p class="max-w-3xl text-sm font-light text-white/80">{{ $package['pricing_summary'] ?? '' }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-white/15 px-4 py-2 text-sm font-medium backdrop-blur">
                                {{ $package['category'] }}
                            </span>
                            <span class="rounded-full bg-white/15 px-4 py-2 text-sm font-medium backdrop-blur">
                                Jam & durasi dipilih di form
                            </span>
                            @if ($reviewCount > 0)
                                <span class="rounded-full bg-white/15 px-4 py-2 text-sm font-medium backdrop-blur">
                                    {{ number_format($reviewAverage, 1) }}/5 dari {{ $reviewCount }} ulasan
                                </span>
                            @endif
                        </div>
                        <div>
                            <div class="space-y-2">
                                <h2 class="text-lg font-semibold">Fasilitas Reservasi</h2>
                                <div class="pl-5 [&_ul]:list-disc [&_ol]:list-decimal [&_li]:list-item">
                                    <ul class="space-y-2">
                                        @foreach ($package['facilities'] as $facility)
                                            <li>{{ $facility }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <h2 class="text-lg font-semibold">Catatan Reservasi</h2>
                    <div class="pl-5 [&_ul]:list-disc [&_ol]:list-decimal [&_li]:list-item">
                        <ul class="space-y-2">
                            @foreach ($package['notes'] as $note)
                                <li>{{ $note }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="min-h-screen">
                    @include('guest.booking.booking-review')
                </div>
            </div>

            <div class="flex-1">
                <div class="sticky top-20">
                    @include('guest.booking.booking-form')
                </div>
            </div>
        </div>

        @if (filled($paymentSnapToken) && filled($midtransClientKey))
            @push('scripts')
                <script src="{{ $midtransSnapJsUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
                <script>
                    window.addEventListener('load', function() {
                        const snapToken = @js($paymentSnapToken);
                        const fallbackOrderId = @js($paymentOrderId);
                        const profileUrl = @js(route('customer.profile'));
                        const loadingEventName = 'booking-midtrans-loading';
                        const minimumLoadingDuration = 700;

                        if (!snapToken) {
                            return;
                        }

                        const loadingStartedAt = Date.now();

                        const dispatchLoadingState = (open, detail = {}) => {
                            window.dispatchEvent(new CustomEvent(loadingEventName, {
                                detail: {
                                    open,
                                    ...detail,
                                },
                            }));
                        };

                        const buildProfileUrl = (orderId) => {
                            const url = new URL(profileUrl, window.location.origin);

                            if (orderId) {
                                url.searchParams.set('midtrans_order_id', orderId);
                            }

                            return url.toString();
                        };

                        const waitForSnap = (timeoutMs = 8000, intervalMs = 150) => new Promise((resolve, reject) => {
                            if (window.snap?.pay) {
                                resolve(window.snap);

                                return;
                            }

                            const startedAt = Date.now();
                            const timerId = window.setInterval(() => {
                                if (window.snap?.pay) {
                                    window.clearInterval(timerId);
                                    resolve(window.snap);

                                    return;
                                }

                                if ((Date.now() - startedAt) >= timeoutMs) {
                                    window.clearInterval(timerId);
                                    reject(new Error('Midtrans Snap belum siap.'));
                                }
                            }, intervalMs);
                        });

                        const openSnapPayment = () => {
                            const remainingDelay = Math.max(0, minimumLoadingDuration - (Date.now() - loadingStartedAt));

                            window.setTimeout(() => {
                                dispatchLoadingState(false);

                                try {
                                    window.snap.pay(snapToken, {
                                        onSuccess: function(result) {
                                            window.location.href = buildProfileUrl(result?.order_id || fallbackOrderId);
                                        },
                                        onPending: function() {},
                                        onError: function() {},
                                        onClose: function() {},
                                    });
                                } catch (error) {
                                    window.location.href = buildProfileUrl(fallbackOrderId);
                                }
                            }, remainingDelay);
                        };

                        dispatchLoadingState(true, {
                            title: 'Menyiapkan Pembayaran',
                            message: 'Reservasi berhasil dibuat. Popup pembayaran Midtrans akan segera tampil.',
                        });

                        waitForSnap()
                            .then(() => openSnapPayment())
                            .catch(() => {
                                window.location.href = buildProfileUrl(fallbackOrderId);
                            });
                    });
                </script>
            @endpush
        @endif

        @include('guest.components.site-footer')
    </div>
</x-layouts.app>
