@php
    $initial = strtoupper(substr($item['name'] ?? 'M', 0, 1));
@endphp
<article
  x-show='isVisible(@js($item))'
  x-transition.opacity.duration.250ms
  class="menu-card overflow-hidden rounded-[2rem] border border-coffee-100 bg-white"
>
  <div class="relative flex h-44 items-center justify-center bg-coffee-50">
    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-white/80 text-4xl font-light text-coffee-400 ring-1 ring-coffee-100">
      {{ $initial }}
    </div>
    <span class="absolute left-5 top-5 rounded-full bg-coffee-900 px-4 py-2 text-xs font-black uppercase tracking-[0.18em] text-white">
      {{ $item['badge'] }}
    </span>
  </div>
  <div class="p-6">
    <div class="flex items-start justify-between gap-4">
      <div>
        <p class="text-xs font-black uppercase tracking-[0.22em] text-coffee-400">{{ $item['category'] }}</p>
        <h2 class="mt-2 text-xl font-black text-coffee-900">{{ $item['name'] }}</h2>
      </div>
      <p class="whitespace-nowrap rounded-full bg-black px-4 py-2 text-sm font-black text-white">
        Rp {{ number_format((int) $item['price'], 0, ',', '.') }}
      </p>
    </div>
    <p class="mt-4 min-h-12 text-sm leading-6 text-coffee-600">{{ $item['description'] }}</p>
    <button
      type="button"
      class="mt-6 w-full rounded-full bg-black px-5 py-3 text-sm font-black uppercase tracking-[0.16em] text-white transition hover:-translate-y-0.5"
      @click='addItem(@js($item))'
    >
      Tambah ke Cart
    </button>
  </div>
</article>
