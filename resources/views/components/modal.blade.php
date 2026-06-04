@props([
    'open' => 'isOpen',
    'onClose' => null,
    'title' => null,
    'maxWidth' => 'max-w-lg',
    'wrapperClass' => 'relative flex min-h-full items-center justify-center px-4 py-6',
    'panelClass' => 'rounded-lg bg-white p-6 shadow-lg',
    'overlayClass' => 'bg-black/50',
    'showCloseButton' => true,
    'closeLabel' => 'Tutup',
    'hideHeader' => false,
])

@php
    $closeAction = $onClose ?: "{$open} = false";
    $renderHeader = ! $hideHeader && ($title || $showCloseButton);
@endphp

<div x-cloak x-show="{{ $open }}" x-transition.opacity class="fixed inset-0 z-50"
    @keydown.escape.window="{{ $closeAction }}">
    <div class="absolute inset-0 {{ $overlayClass }}" @click="{{ $closeAction }}"></div>

    <div class="{{ $wrapperClass }}">
        <div x-show="{{ $open }}" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full {{ $maxWidth }} {{ $panelClass }}">
            @if ($renderHeader)
                <div class="mb-4 flex items-start gap-4">
                    <div class="flex-1">
                        @if ($title)
                            <h2 class="text-xl font-semibold text-gray-900">{{ $title }}</h2>
                        @endif
                    </div>

                    @if ($showCloseButton)
                        <button type="button" @click="{{ $closeAction }}"
                            class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50 hover:text-gray-900">
                            {{ $closeLabel }}
                        </button>
                    @endif
                </div>
            @endif

            {{ $slot }}

            @isset($footer)
                <div class="mt-6">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
