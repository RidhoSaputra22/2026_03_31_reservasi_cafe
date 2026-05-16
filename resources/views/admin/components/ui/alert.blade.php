{{--
    Reusable Alert Component

    @param string $type - Alert type: info, success, warning, error
    @param bool $dismissible - Allow dismissing the alert
    @param string $icon - Custom icon slot
--}}

@props([
    'type' => 'info',
    'dismissible' => false,
])

@php
    $typeClass = match($type) {
        'info' => 'alert-info',
        'success' => 'alert-success',
        'warning' => 'alert-warning',
        'error' => 'alert-error',
        default => 'alert-info',
    };

    $iconSvg = match($type) {
        'info' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        'success' => '<svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
        'warning' => '<svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>',
        'error' => '<svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
        default => '',
    };

    $hasActions = isset($actions) || $dismissible;
@endphp

<div
    {{ $attributes->merge(['class' => "alert  {$typeClass}"]) }}
    @if($dismissible)
        x-data="{ show: true }"
        x-show="show"
        x-transition
    @endif
>
    @if(isset($icon))
        {{ $icon }}
    @else
        {!! $iconSvg !!}
    @endif

    <div @class([
        'flex w-full min-w-0 gap-3',
        'items-start' => $hasActions,
        'sm:items-center sm:justify-between' => $hasActions,
    ])>
        <div class="min-w-0 flex-1">
            {{ $slot }}
        </div>

    </div>
    @if($hasActions)
        <div class="flex shrink-0 items-center gap-2 self-start sm:self-center">
            @if(isset($actions))
                {{ $actions }}
            @endif

            @if($dismissible)
                <x-ui.button type="ghost" size="sm" :isSubmit="false" @click="show = false">
                    ✕
                </x-ui.button>
            @endif
        </div>
    @endif
</div>
