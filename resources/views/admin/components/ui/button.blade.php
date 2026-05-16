{{--
    Reusable Button Component

    @param string $type - Button type: primary, secondary, accent, ghost, link, info, success, warning, error
    @param string $size - Button size: xs, sm, md, lg
    @param bool $outline - Outline style
    @param bool $loading - Show loading state
    @param string $href - If set, renders as anchor tag
    @param bool $disabled - Disabled state
    @param bool $isSubmit - Whether the button is a submit button (default: true)

--}}

@props([
    'type' => 'primary', // DaisyUI: primary, secondary, accent, ghost, link, info, success, warning, error, neutral
    'size' => 'md', // DaisyUI: xs, sm, md, lg
    'outline' => false,
    'loading' => false,
    'href' => null,
    'disabled' => false,
    'isSubmit' => true,
])


@php
    // DaisyUI button classes
    $typeClass = match($type) {
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        'accent' => 'btn-accent',
        'ghost' => 'btn-ghost',
        'link' => 'btn-link',
        'info' => 'btn-info',
        'success' => 'btn-success',
        'warning' => 'btn-warning',
        'error' => 'btn-error',
        'neutral' => 'btn-neutral',
        default => 'btn-primary',
    };

    $sizeClass = match($size) {
        'xs' => 'btn-xs',
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
        default => '',
    };

    $classes = 'btn ' . $typeClass . ' ' . $sizeClass ;
    if ($outline) {
        $classes .= ' btn-outline';
    }
    if ($loading) {
        $classes .= ' loading';
    }
    if ($disabled) {
        $classes .= ' btn-disabled';
    }
@endphp

@php
    $hasDynamicHref = $attributes->has('x-bind:href') || $attributes->has(':href');
    $renderAsLink = filled($href) || $hasDynamicHref;
@endphp

@if($renderAsLink)
    <a @if($href) href="{{ $href }}" @elseif($hasDynamicHref) href="#" @endif
        {{ $attributes->merge(['class' => $classes]) }} @if($disabled) aria-disabled="true" tabindex="-1" @endif>
        @if($loading)
            <span class="loading loading-spinner loading-sm"></span>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $isSubmit ? 'submit' : 'button' }}"
        {{ $attributes->merge(['class' => $classes]) }}
        @disabled($disabled)>
        @if($loading)
            <span class="loading loading-spinner loading-sm"></span>
        @endif
        {{ $slot }}
    </button>
@endif
