<header class="sticky top-0 z-40 px-4 pt-4">
    <nav
        class="mx-auto  flex max-w-7xl items-center gap-4 rounded-full border border-white/60 bg-white/90 px-6 py-4 text-primary shadow-lg backdrop-blur">
        <a href="{{ route('landing') }}" class="flex items-center gap-3">
            <img src="{{ asset('assets/images/logo.jpg') }}" alt="Cafe Amiko"
                class="h-11 w-11 rounded-full object-cover">
            <div>
                <p class="text-[11px] uppercase tracking-[0.35em] text-primary/60">Cafe</p>
                <p class="text-sm font-semibold">{{ data_get($profile ?? null, 'name', 'Amiko') }}</p>
            </div>
        </a>

        <div class="hidden flex-1 items-center justify-center gap-8 text-sm font-medium md:flex">
            <a href="{{ route('landing') }}" class="transition hover:text-primary/70">Beranda</a>
            <a href="{{ route('about') }}" class="transition hover:text-primary/70">Tentang</a>
            <a href="{{ route('packages.index') }}" class="transition hover:text-primary/70">Reservasi</a>
        </div>

        <div class="ml-auto flex items-center gap-3">
            @auth
                <a href="{{ route('customer.profile') }}"
                    class="hidden rounded-full border border-primary/20 px-5 py-3 text-sm font-semibold text-primary transition hover:border-primary hover:bg-primary/5 md:inline-flex">
                    Akun Saya
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="hidden rounded-full border border-primary/20 px-5 py-3 text-sm font-semibold text-primary transition hover:border-primary hover:bg-primary/5 lg:inline-flex">
                        Keluar
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}"
                    class="hidden rounded-full border border-primary/20 px-5 py-3 text-sm font-semibold text-primary transition hover:border-primary hover:bg-primary/5 md:inline-flex">
                    Masuk
                </a>
            @endauth

            <a href="{{ route('packages.index') }}"
                class="inline-flex items-center rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-primary/90">
                Reservasi Sekarang
            </a>
        </div>
    </nav>
</header>
