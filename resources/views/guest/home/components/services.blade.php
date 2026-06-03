@php
    $packages = $featuredPackages ?? collect(config('packages'))
        ->only([
            'coffee-date-corner',
            'work-brew-table',
            'live-music-hangout',
        ])
        ->values()
        ->all();
@endphp

<div>
    <section class="bg-primary ">
        <div class="mx-auto min-h-screen w-full max-w-7xl space-y-14 px-6 py-20 text-white md:p-24">
            <div class="space-y-5 flex-1 pt-8 text-center" data-aos="fade-up">
                <h1 class="text-6xl/tight font-semibold">Reservasi Favorit di Cafe Amiko</h1>
                <p class="text-md/loose font-light ">
                    Pilih tipe kunjungan yang paling cocok untuk cara kamu menikmati Amiko.
                </p>
            </div>
            <div class="flex-1 " data-aos="fade-up">
                <img src="{{ asset('assets/images/hero.jpg') }}" alt="Reservasi unggulan Cafe Amiko"
                    class="w-full rounded-md object-cover shadow-2xl">
            </div>
            <div class="py-14 ">
                @foreach ($packages as $key => $item)
                <a href="{{ route('booking.show', ['slug' => $item['slug']]) }}"
                    class="block transform transition-transform duration-300 ease-in-out will-change-transform hover:scale-[1.01]">

                    <div class="flex min-h-[38rem] flex-col gap-10 pb-16 cursor-pointer md:min-h-screen md:pb-24 {{ ($key + 1) % 2 == 0 ? 'md:flex-row-reverse' : 'md:flex-row' }}"
                        data-aos="fade-up">
                        <div class="flex-1">
                            <img src="{{ asset($item['image']) }}" alt="{{ $item['name'] }}"
                                class="h-full w-full rounded-md object-cover shadow-xl">
                        </div>
                        <div class="flex-1 overflow-hidden space-y-5">
                            <h1 class="text-5xl/relaxed font-semibold uppercase">
                                {{ $item['name'] }}</h1>
                            <h1 class="text-5xl/relaxed font-semibold ">{{ $item['price'] }}</h1>
                            <p class="text-xl/relaxed font-light text-justify">
                                {{ $item['description'] }}
                            </p>
                            <div class="space-y-5 text-xl/relaxed font-light text-justify">
                                <p class="text-md/tight font-medium text-white/80">{{ $item['pricing_summary'] ?? 'Durasi fleksibel, dipilih saat reservasi.' }}</p>
                            </div>
                            <div class="space-y-2  ">
                                <h2 class="text-lg font-semibold">Fasilitas</h2>
                                <div class="pl-5 [&_ul]:list-disc [&_ol]:list-decimal [&_li]:list-item space-y-4">
                                    <ul class="space-y-2">
                                        @foreach ($item['facilities'] as $facility)
                                        <li>{{ $facility }}</li>
                                        @endforeach
                                    </ul>
                                </div>

                            </div>
                            <div class="space-y-2  ">
                                <h2 class="text-lg font-semibold">Keterangan</h2>
                                <div class="pl-5 [&_ul]:list-disc [&_ol]:list-decimal [&_li]:list-item space-y-4">
                                    <ul class="space-y-2">
                                        @foreach ($item['notes'] as $note)
                                        <li>{{ $note }}</li>
                                        @endforeach
                                    </ul>
                                </div>

                            </div>


                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>

    </section>

</div>
