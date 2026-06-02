<x-layouts.app>
    <div>
        @include('guest.components.site-navbar')

        <div class="min-h-screen">
            @component('guest.home.components.about-us')

            @endcomponent

            <section class="w-full bg-primary px-6 py-16 text-white md:px-24">
                <div class="flex flex-col gap-10 md:flex-row md:items-start">
                    <div class="flex-1 space-y-6" data-aos="fade-up">
                        <div class="space-y-2">
                            <h2 class="text-4xl/tight font-semibold">Kontak Kami</h2>
                            <p class="text-sm/relaxed font-light">
                                Jika ingin bertanya tentang reservasi, kapasitas meja, atau agenda komunitas di Cafe
                                Amiko, silakan gunakan informasi berikut.
                            </p>
                        </div>

                        <div class="space-y-4">
                            <div class="space-y-1">
                                <p class="text-sm font-semibold">Alamat</p>
                                <p class="text-sm/relaxed font-light">
                                    {{ $profile->address ?? 'Jl. Meranti No.215, Paropo, Kec. Panakkukang, Kota Makassar, Sulawesi Selatan 90221' }}
                                </p>
                            </div>

                            <div class="space-y-1">
                                <p class="text-sm font-semibold">Konsep</p>
                                <p class="text-sm/relaxed font-light">
                                    {{ $profile->description ?? 'Creative coffee space 24 jam yang memadukan kopi, musik, dan pengalaman komunitas.' }}
                                </p>
                            </div>

                            @if (! empty($profile?->phone_number))
                                <div class="space-y-1">
                                    <p class="text-sm font-semibold">Telepon</p>
                                    <p class="text-sm/relaxed font-light">
                                        {{ $profile->phone_number }}
                                    </p>
                                </div>
                            @endif

                            <div class="space-y-1">
                                <p class="text-sm font-semibold">Maps</p>
                                <a class="text-sm font-light underline" target="_blank" rel="noopener noreferrer"
                                    href="https://www.google.com/maps/search/?api=1&query={{ urlencode($profile->address ?? 'Jl. Meranti No.215, Paropo, Kec. Panakkukang, Kota Makassar, Sulawesi Selatan 90221') }}">
                                    Buka di Google Maps
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1" data-aos="fade-up">
                        <div class="rounded-2xl overflow-hidden bg-white">
                            <div class="relative w-full" style="padding-top: 56.25%;">
                                <iframe class="absolute inset-0 w-full h-full" loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    src="https://www.google.com/maps?q={{ urlencode($profile->address ?? 'Jl. Meranti No.215, Paropo, Kec. Panakkukang, Kota Makassar, Sulawesi Selatan 90221') }}&output=embed">
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        @include('guest.components.site-footer')
    </div>
</x-layouts.app>
