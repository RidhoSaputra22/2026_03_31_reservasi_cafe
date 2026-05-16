@php
    $eyebrow = $eyebrow ?? '';
    $title = $title ?? '';
    $description = $description ?? '';
@endphp
<section class="pt-28">
  <div class="mx-auto max-w-7xl px-5 py-12 lg:px-8">
    <div class="max-w-3xl">
      @if ($eyebrow)
        <p class="text-sm font-black uppercase tracking-[0.28em] text-coffee-400">{{ $eyebrow }}</p>
      @endif
      <h1 class="mt-4 text-4xl font-light tracking-wide text-coffee-800 sm:text-5xl">{{ $title }}</h1>
      @if ($description)
        <p class="mt-4 max-w-2xl leading-7 text-coffee-600">{{ $description }}</p>
      @endif
    </div>
  </div>
</section>
