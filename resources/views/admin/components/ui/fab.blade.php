{{--
    Reusable Floating Action Button Panel

    @param string $triggerText - Fallback trigger content when no trigger slot is provided
    @param string $triggerAriaLabel - Accessible label for the closed state trigger
    @param string $triggerClass - Classes for the closed state trigger button
    @param string $triggerType - Button type for the trigger button
    @param string|null $mainActionText - Fallback open-state trigger content, defaults to triggerText
    @param string|null $mainActionAriaLabel - Accessible label for the open state trigger
    @param string $mainActionClass - Classes for the open state trigger button
    @param string $mainActionType - Button type for the open-state trigger button
    @param string|null $panelTitle - Optional title shown in the action panel
    @param string|null $panelDescription - Optional helper text shown below the panel title
    @param string $panelClass - Extra classes for the action panel
    @slot trigger - Custom trigger content for the closed state
    @slot mainAction - Custom trigger content for the open state
    @slot $slot - FAB actions content
--}}

@props([
    'triggerText' => '+',
    'triggerAriaLabel' => 'Buka menu aksi',
    'triggerClass' => 'btn btn-circle btn-lg btn-primary shadow-xl',
    'triggerType' => 'button',
    'mainActionText' => null,
    'mainActionAriaLabel' => null,
    'mainActionClass' => 'btn btn-circle btn-lg btn-secondary shadow-xl',
    'mainActionType' => 'button',
    'panelTitle' => null,
    'panelDescription' => null,
    'panelClass' => '',
])

@php
    $triggerContent = isset($trigger) ? $trigger : $triggerText;
    $resolvedMainActionText = $mainActionText ?? $triggerText;
    $resolvedMainActionAriaLabel = $mainActionAriaLabel ?? $triggerAriaLabel;
    $resolvedMainActionContent = isset($mainAction) ? $mainAction : $resolvedMainActionText;
    $panelId = 'context-fab-' . \Illuminate\Support\Str::slug(($panelTitle ?: $triggerAriaLabel) . '-panel');
@endphp

<div x-data="{ open: false }" @fab-close.window="open = false" @keydown.escape.window="open = false"
    class="pointer-events-none fixed bottom-5 right-4 z-[70] flex items-end justify-end sm:bottom-6 sm:right-6">
    <div x-cloak x-show="open" x-transition.opacity.duration.200ms class="pointer-events-auto fixed inset-0"
        @click="open = false" aria-hidden="true"></div>

    <div class="relative z-[1] flex flex-col items-end gap-3">
        <div x-cloak x-show="open" x-transition.origin.bottom.right.duration.200ms id="{{ $panelId }}"
            class="{{ trim('pointer-events-auto w-[min(22rem,calc(100vw-1.5rem))] overflow-hidden rounded-md border border-base-300/70 bg-base-100/95 p-3 shadow-2xl ring-1 ring-black/5 backdrop-blur ' . $panelClass) }}">
            @if ($panelTitle || $panelDescription)
                <div class="mb-3 px-1">
                    @if ($panelTitle)
                        <p class="text-sm font-semibold text-base-content">{{ $panelTitle }}</p>
                    @endif

                    @if ($panelDescription)
                        <p class="mt-1 text-xs leading-relaxed text-base-content/65">{{ $panelDescription }}</p>
                    @endif
                </div>
            @endif

            <div class="max-h-[calc(100vh-9rem)] space-y-2 overflow-y-auto pr-1">
                {{ $slot }}
            </div>
        </div>

        <button type="{{ $triggerType }}" @click="open = !open" :aria-expanded="open.toString()"
            aria-controls="{{ $panelId }}" :aria-label="open ? '{{ $resolvedMainActionAriaLabel }}' : '{{ $triggerAriaLabel }}'"
            class="pointer-events-auto transition duration-200 ease-out hover:-translate-y-0.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-base-content/20 focus-visible:ring-offset-2 focus-visible:ring-offset-base-100"
            :class="open ? '{{ $mainActionClass }}' : '{{ $triggerClass }}'">
            <span x-cloak x-show="!open" x-transition.opacity.duration.150ms>
                {{ $triggerContent }}
            </span>

            <span x-cloak x-show="open" x-transition.opacity.duration.150ms>
                {{ $resolvedMainActionContent }}
            </span>
        </button>
    </div>
</div>
