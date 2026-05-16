{{--
    Reusable Tabs Container

    @param string $variant - boxed, plain, lifted
    @param string $layout - inline-flex, flex, grid
    @param bool $compact - Smaller spacing/padding
    @param string $navClass - Additional class for the tablist
    @param string $panelsClass - Additional class for the panels wrapper
--}}

@props([
    'variant' => 'boxed',
    'layout' => 'inline-flex',
    'compact' => false,
    'navClass' => '',
    'panelsClass' => 'mt-4',
])

@php
    $variantClass = match ($variant) {
        'plain' => '',
        'lifted' => 'tabs-lifted',
        default => 'tabs-boxed bg-base-200/80 p-1 rounded-xl',
    };

    $layoutClass = match ($layout) {
        'grid' => 'grid',
        'flex' => 'flex',
        default => 'inline-flex',
    };

    $compactClass = $compact ? 'gap-1' : 'gap-2';
@endphp

<div {{ $attributes }}>
    <div class="{{ trim('tabs ' . $layoutClass . ' ' . $variantClass . ' ' . $compactClass . ' ' . $navClass) }}"
        role="tablist">
        {{ $nav ?? $slot }}
    </div>

    @if (isset($panels))
        <div class="{{ $panelsClass }}">
            {{ $panels }}
        </div>
    @endif
</div>
