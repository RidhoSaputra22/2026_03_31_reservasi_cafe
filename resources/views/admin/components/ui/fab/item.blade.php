{{--
    Reusable FAB Action Item

    @param string|null $tooltip - Primary action label
    @param string|null $description - Secondary helper text
    @param string $wrapperClass - Extra classes for the clickable wrapper
    @param string|null $tag - Supported: button, a, div. Defaults to a when href exists, otherwise button
    @param string $type - Button type when tag is button
    @param string|null $href - Link target when tag is a
    @param string|null $ariaLabel - Accessible label, defaults to tooltip
    @param string $buttonClass - Classes for the icon surface
    @param bool $active - Whether the action should appear highlighted
--}}

@props([
    'tooltip' => null,
    'description' => null,
    'wrapperClass' => '',
    'tag' => null,
    'type' => 'button',
    'href' => null,
    'ariaLabel' => null,
    'buttonClass' => 'btn btn-circle btn-lg',
    'active' => false,
])

@php
    $resolvedTag = $tag ?? ($href ? 'a' : 'button');
    $resolvedAriaLabel = $ariaLabel ?? $tooltip;
    $resolvedWrapperClass = trim(implode(' ', array_filter([
        'group flex w-full items-center gap-3 rounded-md border px-3 py-3 text-left transition duration-200 ease-out hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/25',
        $active ? 'border-primary/30 bg-primary/10 shadow-sm' : 'border-base-300/70 bg-base-100 shadow-sm hover:border-base-300',
        $wrapperClass,
    ])));
@endphp

@if ($resolvedTag === 'a')
    <a href="{{ $href ?? '#' }}" x-on:click="$dispatch('fab-close')"
        @if ($resolvedAriaLabel) aria-label="{{ $resolvedAriaLabel }}" @endif
        {{ $attributes->class([$resolvedWrapperClass]) }}>
        <span class="{{ $buttonClass }} pointer-events-none shrink-0">
            {{ $slot }}
        </span>

        @if ($tooltip || $description)
            <span class="min-w-0 flex-1">
                @if ($tooltip)
                    <span class="block truncate text-sm font-semibold text-base-content">{{ $tooltip }}</span>
                @endif

                @if ($description)
                    <span class="mt-1 block text-xs leading-relaxed text-base-content/60">{{ $description }}</span>
                @endif
            </span>
        @endif
    </a>
@elseif ($resolvedTag === 'div')
    <div role="button" tabindex="0" x-on:click="$dispatch('fab-close')"
        @if ($resolvedAriaLabel) aria-label="{{ $resolvedAriaLabel }}" @endif
        {{ $attributes->class([$resolvedWrapperClass]) }}>
        <span class="{{ $buttonClass }} pointer-events-none shrink-0">
            {{ $slot }}
        </span>

        @if ($tooltip || $description)
            <span class="min-w-0 flex-1">
                @if ($tooltip)
                    <span class="block truncate text-sm font-semibold text-base-content">{{ $tooltip }}</span>
                @endif

                @if ($description)
                    <span class="mt-1 block text-xs leading-relaxed text-base-content/60">{{ $description }}</span>
                @endif
            </span>
        @endif
    </div>
@else
    <button type="{{ $type }}" x-on:click="$dispatch('fab-close')"
        @if ($resolvedAriaLabel) aria-label="{{ $resolvedAriaLabel }}" @endif
        {{ $attributes->class([$resolvedWrapperClass]) }}>
        <span class="{{ $buttonClass }} pointer-events-none shrink-0">
            {{ $slot }}
        </span>

        @if ($tooltip || $description)
            <span class="min-w-0 flex-1">
                @if ($tooltip)
                    <span class="block truncate text-sm font-semibold text-base-content">{{ $tooltip }}</span>
                @endif

                @if ($description)
                    <span class="mt-1 block text-xs leading-relaxed text-base-content/60">{{ $description }}</span>
                @endif
            </span>
        @endif
    </button>
@endif
