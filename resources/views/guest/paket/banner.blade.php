@php
    $banners = [
        asset('assets/images/hero.jpg'),
        asset('assets/images/about.png'),
        asset('assets/images/hero.jpg'),
    ];
@endphp

<section class="">
    <!-- Swiper -->
    <div class="lg:p-12">
        <div class="swiper packageBannerSwiper h-[18rem] md:h-[26rem] lg:rounded-2xl">
            <div class="w-full h-full swiper-wrapper">
                @foreach ($banners as $item)
                <img class="swiper-slide h-full w-full object-cover object-center" src="{{ $item }}" alt="Banner reservasi Cafe Amiko">

                @endforeach

            </div>
            <div class="package-banner-pagination swiper-pagination"></div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    const packageBannerSwiper = new Swiper(".packageBannerSwiper", {
        slidesPerView: 1,
        centeredSlides: true,
        loop: true,
        speed: 400,
        // spaceBetween: 30,

        autoplay: {
            delay: 2500,
            disableOnInteraction: false,
        },



        pagination: {
            el: ".package-banner-pagination",

        },
    });
</script>
@endpush
