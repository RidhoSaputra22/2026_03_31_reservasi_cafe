@php
    $activePage = $activePage ?? 'landing';
    $navItems = config('cafe.nav', []);
@endphp
<header class="fixed inset-x-0 top-0 z-50 border-b border-white/10 bg-coffee-900/75 backdrop-blur-xl">
  <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 lg:px-8">
    <a href="{{ route('landing') }}" class="text-left text-white" aria-label="Kembali ke landing page">
      <p class="text-xl font-light tracking-[0.35em]">INTERLUDE</p>
      <p class="text-center text-[11px] font-bold tracking-[0.28em] text-coffee-100">COFFEE &amp; TEA</p>
    </a>

    <button id="mobileMenuBtn" class="rounded-full border border-white/20 px-3 py-2 text-sm font-semibold text-white md:hidden" aria-label="Buka menu">
      Menu
    </button>

    <div class="hidden items-center gap-2 md:flex">
      @foreach ($navItems as $item)
        @php($isActive = $activePage === $item['key'])
        <a
          href="{{ route($item['route']) }}"
          class="rounded-full border px-4 py-2 text-sm font-semibold tracking-wide transition {{ $isActive ? 'border-white/30 bg-white text-coffee-800' : 'border-transparent text-coffee-100 hover:bg-white/10 hover:text-white' }}"
        >
          {{ $item['label'] }}
          @if ($item['key'] === 'cart')
            <span id="cartCount" class="ml-1 rounded-full {{ $isActive ? 'bg-coffee-800 text-white' : 'bg-white text-coffee-800' }} px-2 py-0.5 text-xs">0</span>
          @endif
        </a>
      @endforeach
    </div>
  </nav>

  <div id="mobileMenu" class="hidden border-t border-white/10 bg-coffee-900/95 px-5 pb-5 md:hidden">
    <div class="grid gap-2 pt-3">
      @foreach ($navItems as $item)
        <a href="{{ route($item['route']) }}" class="rounded-2xl px-4 py-3 text-left font-semibold text-coffee-50 hover:bg-white/10">
          {{ $item['label'] }}
        </a>
      @endforeach
    </div>
  </div>
</header>
