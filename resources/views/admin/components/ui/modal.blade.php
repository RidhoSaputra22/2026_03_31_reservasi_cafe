@props([
    'id',
    'title' => null,
    'size' => 'md',
    'closeButton' => true,
    'centered' => true,
])

@php
    $sizeClass = match($size) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-lg',
        'lg' => 'max-w-3xl',
        'xl' => 'max-w-5xl',
        '2xl' => 'max-w-7xl',
        default => 'max-w-lg',
    };

    $modalPositionClass = $centered ? 'modal-middle' : 'modal-bottom sm:modal-middle';

    $modalActionsPosition = isset($modalActions)
        ? trim($modalActions->attributes->get('position', 'top-right') ?? 'top-right')
        : null;

    $topPositions = ['top-left', 'top-center', 'top-right'];
    $bottomPositions = ['bottom-left', 'bottom-center', 'bottom-right'];

    $hasTopActions = isset($modalActions) && in_array($modalActionsPosition, $topPositions, true);
    $hasBottomActions = isset($modalActions) && in_array($modalActionsPosition, $bottomPositions, true);

    $hasHeader = isset($titleSlot) || $title || $closeButton || $hasTopActions;
@endphp

<dialog id="{{ $id }}" class="modal {{ $modalPositionClass }}">
    <div class="modal-box {{ $sizeClass }}">
        @if($hasHeader)
            <div class="mb-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1 text-left">
                        @if(isset($titleSlot))
                            {{ $titleSlot }}
                        @elseif($title)
                            <h3 class="font-bold text-lg">{{ $title }}</h3>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        @if($hasTopActions && $modalActionsPosition === 'top-right')
                            {{ $modalActions }}
                        @endif

                        @if($closeButton)
                            <form method="dialog">
                                <x-ui.button type="ghost" size="sm" class="btn-circle">✕</x-ui.button>
                            </form>
                        @endif
                    </div>
                </div>

                @if($hasTopActions && $modalActionsPosition === 'top-left')
                    <div class="mt-3 flex justify-start">
                        {{ $modalActions }}
                    </div>
                @endif

                @if($hasTopActions && $modalActionsPosition === 'top-center')
                    <div class="mt-3 flex justify-center">
                        {{ $modalActions }}
                    </div>
                @endif
            </div>
        @endif

        {{ $slot }}

        @if($hasBottomActions)
            <div class="mt-4">
                @if($modalActionsPosition === 'bottom-left')
                    <div class="flex justify-start">
                        {{ $modalActions }}
                    </div>
                @elseif($modalActionsPosition === 'bottom-center')
                    <div class="flex justify-center">
                        {{ $modalActions }}
                    </div>
                @elseif($modalActionsPosition === 'bottom-right')
                    <div class="flex justify-end">
                        {{ $modalActions }}
                    </div>
                @endif
            </div>
        @endif

        @if(isset($actions))
            <div class="modal-action">
                {{ $actions }}
            </div>
        @endif
    </div>

    <form method="dialog" class="modal-backdrop">
        <x-ui.button class="hidden">close</x-ui.button>
    </form>
</dialog>
