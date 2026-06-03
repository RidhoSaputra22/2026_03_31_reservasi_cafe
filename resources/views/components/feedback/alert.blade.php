@props([
    'type' => 'info',
    'title' => null,
    'autoClose' => 3000,
])

@php
    $config = [
        'success' => [
            'title' => 'Berhasil',
            'class' => 'guest-flash--success',
            'icon' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9.55 16.2 5.3 11.95l1.4-1.4 2.85 2.85 7-7 1.4 1.4-8.4 8.4Z" fill="currentColor"/></svg>',
        ],
        'error' => [
            'title' => 'Terjadi kendala',
            'class' => 'guest-flash--error',
            'icon' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.75A9.25 9.25 0 1 0 21.25 12 9.26 9.26 0 0 0 12 2.75Zm3.3 11.14-1.41 1.41L12 13.41l-1.89 1.89-1.41-1.41L10.59 12 8.7 10.11l1.41-1.41L12 10.59l1.89-1.89 1.41 1.41L13.41 12Z" fill="currentColor"/></svg>',
        ],
        'warning' => [
            'title' => 'Perhatian',
            'class' => 'guest-flash--warning',
            'icon' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3.75 2.75 19.5h18.5L12 3.75Zm0 11.5a1 1 0 1 1 0 2 1 1 0 0 1 0-2Zm1-2.5h-2v-4h2v4Z" fill="currentColor"/></svg>',
        ],
        'info' => [
            'title' => 'Informasi',
            'class' => 'guest-flash--info',
            'icon' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.75A9.25 9.25 0 1 0 21.25 12 9.26 9.26 0 0 0 12 2.75Zm.88 13.5h-1.75v-5h1.75v5Zm0-7.25h-1.75V7.75h1.75V9Z" fill="currentColor"/></svg>',
        ],
    ][$type] ?? [
        'title' => 'Informasi',
        'class' => 'guest-flash--info',
        'icon' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.75A9.25 9.25 0 1 0 21.25 12 9.26 9.26 0 0 0 12 2.75Zm.88 13.5h-1.75v-5h1.75v5Zm0-7.25h-1.75V7.75h1.75V9Z" fill="currentColor"/></svg>',
    ];

    $heading = filled($title) ? $title : $config['title'];
    $autoClose = max(0, (int) $autoClose);
    $role = in_array($type, ['error', 'warning'], true) ? 'alert' : 'status';
@endphp

<div
    x-cloak
    x-data="{
        visible: true,
        timeoutId: null,
        close() {
            this.visible = false;

            if (this.timeoutId) {
                window.clearTimeout(this.timeoutId);
            }
        },
    }"
    @if ($autoClose > 0)
        x-init="timeoutId = window.setTimeout(() => close(), {{ $autoClose }})"
    @endif
    x-show="visible"
    x-transition:enter="transform transition duration-300 ease-out"
    x-transition:enter-start="-translate-y-3 scale-95 opacity-0"
    x-transition:enter-end="translate-y-0 scale-100 opacity-100"
    x-transition:leave="transform transition duration-200 ease-in"
    x-transition:leave-start="translate-y-0 scale-100 opacity-100"
    x-transition:leave-end="-translate-y-2 scale-95 opacity-0"
    role="{{ $role }}"
    aria-live="{{ $role === 'alert' ? 'assertive' : 'polite' }}"
    {{ $attributes->class(['guest-flash', $config['class']]) }}
>
    <div class="guest-flash__icon">
        {!! $config['icon'] !!}
    </div>

    <div class="guest-flash__content">
        @if (filled($heading))
            <p class="guest-flash__title">{{ $heading }}</p>
        @endif

        <div class="guest-flash__message">
            {{ $slot }}
        </div>
    </div>

    <button type="button" class="guest-flash__close" @click="close()" aria-label="Tutup notifikasi">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="m6.4 6.4 11.2 11.2m0-11.2L6.4 17.6" fill="none" stroke="currentColor" stroke-linecap="round"
                stroke-width="2" />
        </svg>
    </button>
</div>
