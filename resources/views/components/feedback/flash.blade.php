@props([
    'fixed' => true,
])

@php
    $messages = [];

    foreach ([
        ['key' => 'success', 'type' => 'success', 'title' => 'Berhasil'],
        ['key' => 'error', 'type' => 'error', 'title' => 'Terjadi kendala'],
        ['key' => 'warning', 'type' => 'warning', 'title' => 'Perhatian'],
        ['key' => 'info', 'type' => 'info', 'title' => 'Informasi'],
    ] as $flash) {
        $message = session($flash['key']);

        if (filled($message)) {
            $messages[] = [
                'type' => $flash['type'],
                'title' => $flash['title'],
                'message' => $message,
            ];
        }
    }

    if ($errors->any()) {
        $messages[] = [
            'type' => 'error',
            'title' => 'Masih ada data yang perlu diperbaiki',
            'list' => $errors->all(),
        ];
    }

    $stackClass = $fixed ? 'guest-flash-stack' : 'guest-flash-stack-inline';
@endphp

@if ($messages !== [])
    <div {{ $attributes->class([$stackClass]) }} aria-live="polite" aria-atomic="true">
        @foreach ($messages as $message)
            <x-feedback.alert :type="$message['type']" :title="$message['title']">
                @if (isset($message['list']))
                    <ul class="guest-flash__list">
                        @foreach ($message['list'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                @else
                    <p>{{ $message['message'] }}</p>
                @endif
            </x-feedback.alert>
        @endforeach
    </div>
@endif
