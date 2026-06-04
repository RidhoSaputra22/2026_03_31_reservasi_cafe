@php
    $guestUser = auth()->user();
    $guestInitials = '';

    if ($guestUser) {
        $nameParts = collect(explode(' ', trim($guestUser->name)))
            ->filter()
            ->take(2)
            ->map(fn(string $part) => strtoupper(substr($part, 0, 1)));

        $guestInitials = $nameParts->implode('');
    }
@endphp

<header class="sticky top-0 z-40 px-4 pt-4">
    <nav
        class="mx-auto  flex max-w-7xl items-center gap-4 rounded-full border border-white/60 bg-white/90 px-6 py-4 text-primary shadow-lg backdrop-blur">
        <a href="{{ route('landing') }}" class="flex items-center gap-3">
            <img src="{{ asset('assets/images/logo.jpg') }}" alt="Cafe Amiko" class="h-11 w-11 rounded-full object-cover">
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
                <div class="relative hidden md:block" x-data="{ open: false }" @keydown.escape.window="open = false">
                    <button type="button" @click="open = !open"
                        class="inline-flex items-center gap-3 rounded-full w-11 h-11 justify-center border text-sm font-semibold text-primary transition hover:border-primary hover:bg-primary/5 cursor-pointer"
                        :aria-expanded="open.toString()" aria-haspopup="true">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </button>

                    <div x-cloak x-show="open" @click.outside="open = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2"
                        class="absolute right-0 z-50 mt-3 w-64 overflow-hidden rounded-md border border-primary/10 bg-white shadow-2xl"
                        style="display: none;">
                        <div class="border-b border-primary/10 px-5 py-4">
                            <p class="truncate text-sm font-semibold text-primary">{{ $guestUser?->name }}</p>
                            <p class="truncate text-xs text-primary/60">{{ $guestUser?->email }}</p>
                        </div>

                        <div class="p-2">
                            <a href="{{ route('customer.profile') }}"
                                class="flex items-center gap-3 rounded-md px-4 py-3 text-sm font-medium text-primary transition hover:bg-primary/5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                                Akun Saya
                            </a>

                            <form method="POST" action="{{ route('logout') }}" data-loading-form>
                                @csrf
                                <button type="submit" data-loading-button
                                    class="guest-loading-button flex w-full items-center gap-3 rounded-md px-4 py-3 text-left text-sm font-medium text-primary transition hover:bg-primary/5 cursor-pointer">
                                    <span class="guest-loading-button__label">
                                        <span class="flex items-center gap-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                            </svg>
                                            <span>Keluar</span>
                                        </span>
                                    </span>
                                    <span class="guest-loading-button__state">
                                        <span class="guest-loading-button__spinner" aria-hidden="true"></span>
                                        <span>Keluar...</span>
                                    </span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
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
