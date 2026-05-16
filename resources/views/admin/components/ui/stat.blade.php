{{--
    Reusable Stats Card Component

    @param string $title - Stat title
    @param string|int $value - Stat value
    @param string $description - Additional description
    @param string $icon - Icon SVG slot
    @param string $trend - Trend direction: up, down, neutral
    @param string $trendValue - Trend percentage or value
--}}

@props([
    'title',
    'value',
    'description' => null,
    'trend' => null,
    'trendValue' => null,
    'href' => null,
])

@php
    $hoverClass = $href
        ? ' cursor-pointer transition duration-200 hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40'
        : '';
@endphp

<div
    {{ $attributes->merge(['class' => 'stat bg-base-100 shadow-md rounded-lg p-4' . $hoverClass]) }}
    @if ($href)
        role="link"
        tabindex="0"
        data-stat-href="{{ $href }}"
        aria-label="Buka {{ $title }}"
        onclick="window.location.href = this.dataset.statHref"
        onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location.href = this.dataset.statHref; }"
    @endif
>
    @if(isset($icon))
        <div class="stat-figure  ">
            {{ $icon }}
        </div>
    @endif

    <div class="stat-title">{{ $title }}</div>
    <div class="stat-value text-primary">{{ $value }}</div>

    @if($description || $trend)
        <div class="stat-desc flex items-center gap-1">
            @if($trend === 'up')
                <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                <span class="text-success">{{ $trendValue }}</span>
            @elseif($trend === 'down')
                <svg class="w-4 h-4 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                </svg>
                <span class="text-error">{{ $trendValue }}</span>
            @endif
            {{ $description }}
        </div>
    @endif
</div>
