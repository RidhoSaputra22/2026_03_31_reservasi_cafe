{{--
    Avatar Component
    Display user avatar with initials or image

    Usage:
    <x-ui.avatar name="Ahmad Yani" size="md" />
    <x-ui.avatar name="Rina" src="/path/to/photo.jpg" size="sm" />
    <x-ui.avatar name="Admin" :online="true" />
--}}

@props([
    'name' => 'User',
    'src' => null,
    'size' => 'md',
    'online' => false,
    'ring' => false,
])

@php
    // Generate initials from name (first 2 characters of first 2 words)
    $words = explode(' ', trim($name));
    $initials = '';

    if (count($words) >= 2) {
        $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    } else {
        $initials = strtoupper(substr($name, 0, 2));
    }

    // Size mapping
    $sizeClasses = [
        'xs' => 'w-6',
        'sm' => 'w-8',
        'md' => 'w-10',
        'lg' => 'w-12',
        'xl' => 'w-16',
        '2xl' => 'w-20',
    ];

    $textSizes = [
        'xs' => 'text-xs',
        'sm' => 'text-xs',
        'md' => 'text-sm',
        'lg' => 'text-base',
        'xl' => 'text-lg',
        '2xl' => 'text-2xl',
    ];

    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
    $textSize = $textSizes[$size] ?? $textSizes['md'];
@endphp

<div {{ $attributes->merge(['class' => 'avatar' . ($online ? ' online' : '') . ($ring ? ' ring ring-primary ring-offset-base-100 ring-offset-2' : '')]) }}>
    <div class="{{ $sizeClass }} rounded-full {{ $src ? '' : 'bg-primary text-primary-content placeholder' }} flex justify-center items-center">
        @if($src)
            <img src="{{ $src }}" alt="{{ $name }}" />
        @else
            <span class="{{ $textSize }}">{{ $initials }}</span>
        @endif
    </div>
</div>
