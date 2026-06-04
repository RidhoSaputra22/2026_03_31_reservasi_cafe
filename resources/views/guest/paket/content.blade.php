@php
    $packages = collect($packages ?? config('packages'))->values()->all();
    $categories = $categories ?? collect($packages)->pluck('category')->unique()->prepend('Semua Kategori')->values()->all();
    $resultsAnchor = 'package-results';
    $filters = $filters ?? [
        'q' => '',
        'category' => '',
        'price' => '',
        'sort' => 'latest',
    ];
@endphp


<section class="p-12 min-h-screen scroll-mt-24" id="{{ $resultsAnchor }}">
    <div class="flex flex-col gap-10 xl:flex-row xl:gap-24">
        {{-- FILTER --}}
        <form method="GET" action="{{ route('packages.index') }}#{{ $resultsAnchor }}"
            class="rounded-lg border border-gray-100 bg-white p-6 xl:w-80" data-loading-form>
            <h1 class="mb-6 text-xl font-semibold">Filter Reservasi</h1>

            <div class="space-y-6">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-primary">Cari Paket</label>
                    <input type="text" name="q" value="{{ $filters['q'] }}"
                        class="w-full rounded-lg border border-gray-200 px-4 py-3"
                        placeholder="Nama paket atau area">
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-primary">Kategori</label>
                    <select name="category" class="w-full rounded-lg border border-gray-200 px-4 py-3">
                        @foreach ($categories as $category)
                            <option value="{{ $category === 'Semua Kategori' ? '' : $category }}"
                                @selected($filters['category'] === ($category === 'Semua Kategori' ? '' : $category))>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-primary">Harga</label>
                    <select name="price" class="w-full rounded-lg border border-gray-200 px-4 py-3">
                        <option value="" @selected($filters['price'] === '')>Semua Harga</option>
                        <option value="asc" @selected($filters['price'] === 'asc')>Rendah ke Tinggi</option>
                        <option value="desc" @selected($filters['price'] === 'desc')>Tinggi ke Rendah</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-primary">Urutkan</label>
                    <select name="sort" class="w-full rounded-lg border border-gray-200 px-4 py-3">
                        <option value="latest" @selected($filters['sort'] === 'latest')>Urutan Default</option>
                        <option value="oldest" @selected($filters['sort'] === 'oldest')>Kebalikan Default</option>
                        <option value="name_asc" @selected($filters['sort'] === 'name_asc')>Nama A-Z</option>
                        <option value="name_desc" @selected($filters['sort'] === 'name_desc')>Nama Z-A</option>
                    </select>
                </div>

                <div class="flex gap-3">
                    <button type="submit" data-loading-button
                        class="guest-loading-button flex-1 rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-white transition hover:bg-primary/90 cursor-pointer">
                        <span class="guest-loading-button__label">Terapkan</span>
                        <span class="guest-loading-button__state">
                            <span class="guest-loading-button__spinner" aria-hidden="true"></span>
                            <span>Menerapkan...</span>
                        </span>
                    </button>
                    <a href="{{ route('packages.index') }}#{{ $resultsAnchor }}"
                        class="rounded-lg border border-gray-200 px-4 py-3 text-sm font-semibold text-primary transition hover:border-primary hover:bg-primary/5">
                        Reset
                    </a>
                </div>
            </div>
        </form>
        <div class="flex-1 space-y-10">
            <div class="flex items-center justify-between text-sm font-light text-gray-500">
                <p>Menampilkan {{ count($packages) }} paket reservasi.</p>
                <p>
                    @if ($filters['q'] || $filters['category'] || $filters['price'])
                        Filter aktif diterapkan.
                    @else
                        Jelajahi semua pilihan reservasi.
                    @endif
                </p>
            </div>

            @if ($packages === [])
                <div class="rounded-3xl border border-dashed border-gray-200 bg-white p-10 text-center text-gray-500">
                    <h2 class="text-xl font-semibold text-primary">Belum ada paket yang cocok.</h2>
                    <p class="mt-3 text-sm font-light">Coba ubah kata kunci atau reset filter untuk melihat pilihan lainnya.</p>
                </div>
            @else
                <div class="grid gap-10 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ($packages as $package)
                    <a href="{{ route('booking.show', ['slug' => $package['slug']]) }}" class="group relative z-0 block rounded-lg border border-gray-100 bg-white p-4 shadow-sm transition hover:z-10 hover:-translate-y-1 hover:shadow-xl">
                        <div class="relative">
                            <img src="{{ asset($package['image']) }}" alt="{{ $package['name'] }}"
                                class="h-60 w-full rounded-lg object-cover">
                            <div
                                class="absolute top-2 left-2 rounded-md bg-primary px-3 py-1 text-sm font-medium text-white">
                                {{ $package['category'] }}</div>
                        </div>
                        <div class="mt-4 space-y-2">
                            <h1 class="truncate text-xl font-light uppercase">
                                {{ $package['name'] }}
                            </h1>
                            <p class="text-sm font-light text-gray-500">{{ $package['summary'] }}</p>
                            <h1 class="mt-2 text-lg font-semibold">{{ $package['price'] }}</h1>
                            <p class="text-sm font-medium text-primary">{{ $package['pricing_summary'] ?? 'Durasi fleksibel, pilih saat reservasi.' }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</section>
