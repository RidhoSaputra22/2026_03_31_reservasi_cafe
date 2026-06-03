@php
    $package = $package ?? collect(config('packages'))->first();
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

        @include('guest.components.site-footer')
    </div>
</x-layouts.app>
