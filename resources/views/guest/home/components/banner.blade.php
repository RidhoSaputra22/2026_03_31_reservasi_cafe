@php
    $slides = [
        [
            'image' => asset('assets/images/hero.jpg'),
            'eyebrow' => 'Creative Coffee Space 24 Jam',
            'title' => 'Nikmati kopi, obrolan, dan suasana hangat di Cafe Amiko.',
            'description' => 'Cafe Amiko hadir sebagai ruang singgah yang ramah untuk nongkrong santai, coffee date, kerja, sampai kumpul komunitas.',
        ],
        [
            'image' => asset('assets/images/about.png'),
            'eyebrow' => 'Ramah dan Inklusif',
            'title' => 'Amiko berarti teman, dan itu terasa di setiap sudut tempat ini.',
            'description' => 'Dari kopi sampai atmosfer, Cafe Amiko dirancang agar tamu bisa terhubung, berkolaborasi, dan menikmati momen dengan nyaman.',
        ],
        [
            'image' => asset('assets/images/hero.jpg'),
            'eyebrow' => 'Reservasi Lebih Mudah',
            'title' => 'Pilih area favoritmu, tentukan jadwal, lalu reservasi tanpa alur yang ribet.',
            'description' => 'Tersedia pilihan reservasi untuk date night, work session, hangout malam, sampai private gathering kecil di Cafe Amiko.',
        ],
    ];
@endphp

<div>
    <section class="relative -mt-24 overflow-hidden pt-24">
        <div class="swiper homeBannerSwiper h-[38rem] md:h-[44rem]">
            <div class="swiper-wrapper">
                @foreach ($slides as $slide)
                    <div class="swiper-slide relative">
                        <img class="h-full w-full object-cover object-center" src="{{ $slide['image'] }}"
                            alt="{{ $slide['title'] }}">
                        <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/40 to-transparent"></div>
                        <div class="absolute inset-0 flex items-center">
                            <div class="mx-auto w-full max-w-7xl px-6 text-white">
                                <div class="max-w-2xl space-y-6">
                                    <p class="text-xs uppercase tracking-[0.4em] text-white/70">
                                        {{ $slide['eyebrow'] }}
                                    </p>
                                    <h1 class="text-4xl/tight font-semibold md:text-6xl/tight">
                                        {{ $slide['title'] }}
                                    </h1>
                                    <p class="max-w-xl text-sm/relaxed font-light text-white/85 md:text-base/relaxed">
                                        {{ $slide['description'] }}
                                    </p>
                                    <div class="flex flex-wrap gap-4">
                                        <a href="{{ route('packages.index') }}"
                                            class="rounded-full bg-white px-6 py-3 text-sm font-semibold text-primary transition hover:bg-white/90">
                                            Lihat Reservasi
                                        </a>
                                        <a href="{{ route('about') }}"
                                            class="rounded-full border border-white/50 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                                            Tentang Amiko
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="home-banner-pagination swiper-pagination"></div>
        </div>
    </section>

    @push('scripts')
        <script>
            const homeBannerSwiper = new Swiper(".homeBannerSwiper", {
                slidesPerView: 1,
                centeredSlides: true,
                loop: true,
                speed: 400,
                autoplay: {
                    delay: 2500,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: ".home-banner-pagination",
                },
            });
        </script>
    @endpush

</div>
