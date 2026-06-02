@php
    $recommendedPackages = $recommendedPackages ?? collect(config('packages'))->values()->all();
@endphp

<div class="space-y-10 px-6 py-20 md:px-12 md:py-24">

    <div class="">
        <h1 class="text-4xl/normal font-semibold">Pilihan Reservasi </h1>
        <p class="text-lg font-light">Pilih area favoritmu di Cafe Amiko.</p>
    </div>

    <div class="grid gap-5 overflow-visible md:grid-cols-2 xl:grid-cols-5">
        @foreach ($recommendedPackages as $package)
            <a href="{{ route('booking.show', ['slug' => $package['slug']]) }}"
                class="group relative z-0 rounded-3xl border border-gray-100 bg-white p-4 shadow-sm transition hover:z-10 hover:-translate-y-1 hover:shadow-xl">
                <div class="relative">
                    <img src="{{ asset($package['image']) }}" alt="{{ $package['name'] }}"
                        class="h-60 w-full rounded-2xl object-cover">
                    <div class="absolute left-3 top-3 rounded-full bg-primary px-3 py-1 text-sm font-medium text-white">
                        {{ $package['category'] }}
                    </div>
                </div>
                <div class="mt-4 space-y-2">
                    <h1 class="truncate text-xl font-light uppercase">
                        {{ $package['name'] }}
                    </h1>
                    <h1 class="mt-2 text-lg font-semibold">{{ $package['price'] }}</h1>
                    <div class="flex items-center gap-2 text-primary">
                        @component('components.icon.clock')
                        @endcomponent

                        <div>{{ $package['duration'] }}</div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
    <div class="flex justify-end">
        <a href="{{ route('packages.index') }}"
            class="rounded-sm bg-primary px-3 py-2 text-center text-white outline-offset-1 transition hover:bg-secondary hover:outline hover:outline-2 hover:outline-primary">
            Lihat Semua Reservasi >>
        </a>
    </div>
</div>
