@php
    $activePage = $activePage ?? 'landing';
    $isLanding = request()->routeIs('landing');
    $navItems = [
        ['key' => 'landing', 'label' => 'Home', 'route' => 'landing'],
        ['key' => 'menu', 'label' => 'Menu', 'route' => 'menu'],
        ['key' => 'cart', 'label' => 'Cart', 'route' => 'cart'],
        ['key' => 'about', 'label' => 'About', 'route' => 'about'],
    ];
@endphp

<header
    x-data="navbar()"
    @keydown.escape.window="close()"
    class="fixed inset-x-0 top-0 z-50 {{ $isLanding ? 'border-b border-white/10 bg-coffee-900/75 backdrop-blur-xl' : 'bg-white' }}"
>
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 lg:px-8">
        <a href="{{ route('landing') }}" class="text-left {{ $isLanding ? 'text-white' : 'text-black' }}" aria-label="Kembali ke landing page">
            <p class="text-xl font-light tracking-[0.35em]">AMIKOSPACE</p>
            <p class="text-[11px] font-bold tracking-[0.28em]">COFFEE &amp; TEA</p>
        </a>

        <button
            type="button"
            class="rounded-full border px-3 py-2 text-sm font-semibold md:hidden {{ $isLanding ? 'border-white/20 text-white' : 'border-coffee-200 text-black' }}"
            aria-controls="mobileMenu"
            :aria-expanded="open.toString()"
            @click="toggle()"
        >
            <span x-text="open ? 'Tutup' : 'Menu'"></span>
        </button>

        <div class="hidden items-center gap-2 md:flex">
            @foreach ($navItems as $item)
                @php($isActive = $activePage === $item['key'])
                <a
                    href="{{ route($item['route']) }}"
                    class="rounded-full px-4 py-2 text-sm font-semibold tracking-wide transition {{ $isLanding ? 'text-white' : 'text-black' }} {{ $isActive ? 'underline' : '' }}"
                >
                    {{ $item['label'] }}
                    @if ($item['key'] === 'cart')
                        <span
                            data-cart-count
                            class="ml-1 rounded-full px-2 py-0.5 text-xs {{ $isActive ? 'bg-black text-white' : ($isLanding ? 'bg-white text-black' : 'bg-coffee-100 text-black') }}"
                            x-text="$store.cart.count"
                        >0</span>
                    @endif
                </a>
            @endforeach
        </div>
    </nav>

    <div
        id="mobileMenu"
        x-cloak
        x-show="open"
        x-transition.origin.top.duration.200ms
        class="border-t border-white/10 bg-coffee-900/95 px-5 pb-5 md:hidden"
    >
        <div class="grid gap-2 pt-3">
            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="rounded-2xl px-4 py-3 text-left font-semibold text-coffee-50 hover:bg-white/10"
                    @click="close()"
                >
                    <span>{{ $item['label'] }}</span>
                    @if ($item['key'] === 'cart')
                        <span class="ml-2 text-sm font-black" x-text="$store.cart.count"></span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</header>
