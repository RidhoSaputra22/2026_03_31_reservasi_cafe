@php
    $galleryImages = [
        asset('assets/images/hero.jpg'),
        asset('assets/images/about.png'),
        asset('assets/images/hero.jpg'),
        asset('assets/images/about.png'),
        asset('assets/images/hero.jpg'),
        asset('assets/images/about.png'),
    ];
@endphp

<div>
    <section class="w-full gap-10 px-6 py-20 space-y-14 md:p-24">
        <div class="  text-primary pt-8" data-aos="fade-up">
            <div class="">
                <h1 class="text-6xl/tight font-semibold">Sudah banyak tamu memilih Cafe Amiko sebagai ruang favorit untuk kopi,
                    obrolan, dan komunitas.</h1>
            </div>
        </div>
        <div class="swiper gallerySwiper h-96 w-full" data-aos="fade-up">
            <div class="swiper-wrapper ">
                @foreach ($galleryImages as $image)
                    <div class="relative swiper-slide h-96 w-full
                        ">
                        <img src="{{ $image }}" alt="Galeri Cafe Amiko"
                            class="h-full w-full object-cover object-center ">
                    </div>
                @endforeach

            </div>
        </div>

    </section>

    @push('scripts')
        <script>
            const testimonialGallerySwiper = new Swiper(".gallerySwiper", {
                slidesPerView: 1.2,
                spaceBetween: 16,
                loop: true,
                speed: 400,
                autoplay: {
                    delay: 2500,
                    disableOnInteraction: false,
                },
                breakpoints: {
                    768: {
                        slidesPerView: 2.2,
                    },
                    1024: {
                        slidesPerView: 4,
                    }
                }
            });
        </script>
    @endpush

</div>
