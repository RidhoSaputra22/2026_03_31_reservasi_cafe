<footer class="bg-primary text-white">
    <div class="mx-auto flex max-w-7xl flex-col gap-10 px-6 py-14 md:flex-row md:justify-between">
        <div class="max-w-md space-y-3">
            <div class="flex items-center gap-3">
                <img src="{{ asset('assets/images/logo.jpg') }}" alt="Cafe Amiko"
                    class="h-11 w-11 rounded-full object-cover">
                <div>
                    <p class="text-[11px] uppercase tracking-[0.35em] text-white/60">Cafe</p>
                    <p class="text-lg font-semibold">{{ data_get($profile ?? null, 'name', 'Amiko') }}</p>
                </div>
            </div>
            <p class="text-sm/relaxed font-light text-white/80">
                {{ data_get($profile ?? null, 'description', 'Creative coffee space yang hangat, ramah, dan hidup untuk kopi, obrolan, musik, dan momen komunitas.') }}
            </p>
        </div>

        <div class="space-y-3 text-sm font-light">
            <p class="font-semibold">Kunjungi Kami</p>
            <p class="max-w-sm text-white/80">
                {{ data_get($profile ?? null, 'address', 'Jl. Meranti No.215, Paropo, Kec. Panakkukang, Kota Makassar, Sulawesi Selatan 90221.') }}
            </p>
            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode(data_get($profile ?? null, 'address', 'Jl. Meranti No.215, Paropo, Kec. Panakkukang, Kota Makassar, Sulawesi Selatan 90221')) }}"
                target="_blank" rel="noopener noreferrer"
                class="inline-block underline">
                Buka lokasi Cafe Amiko
            </a>
        </div>

        <div class="space-y-3 text-sm font-light">
            <p class="font-semibold">Navigasi</p>
            <div class="flex flex-col gap-2 text-white/80">
                <a href="{{ route('landing') }}" class="transition hover:text-white">Beranda</a>
                <a href="{{ route('about') }}" class="transition hover:text-white">Tentang</a>
                <a href="{{ route('packages.index') }}" class="transition hover:text-white">Reservasi</a>
            </div>
        </div>
    </div>
</footer>
