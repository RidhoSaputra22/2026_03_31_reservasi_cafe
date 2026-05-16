{{--
    Reusable Badge Component

    @param string $type - Badge type: primary, secondary, accent, ghost, info, success, warning, error
    @param string $size - Badge size: xs, sm, md, lg
    @param bool $outline - Outline style
--}}

@props([
    'type' => '',
    'size' => 'md',
    'outline' => false,
])

@php
    $typeClass = match($type) {
        'primary' => 'badge-primary',
        'secondary' => 'badge-secondary',
        'accent' => 'badge-accent',
        'ghost' => 'badge-ghost',
        'info' => 'badge-info',
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'error' => 'badge-error',
        'neutral' => 'badge-neutral',
        default => '',
    };

    $sizeClass = match($size) {
        'xs' => 'badge-xs',
        'sm' => 'badge-sm',
        'md' => '',
        'lg' => 'badge-lg',
        default => '',
    };

    $classes = "badge {$typeClass} {$sizeClass}";

    if ($outline) {
        $classes .= ' badge-outline';
    }
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
