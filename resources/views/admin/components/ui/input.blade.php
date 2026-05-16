{{--
    Reusable Input Component

    @param string $name - Input name
    @param string $label - Label text
    @param string $type - Input type (text, email, password, number, etc.)
    @param string $placeholder - Placeholder text
    @param string $value - Current value
    @param string $error - Error message
    @param bool $required - Required field
    @param string $helpText - Helper text below input
    @param string $size - Input size (xs, sm, md, lg)
--}}

@props([
    'name' => 'null',
    'label' => null,
    'type' => 'text',
    'placeholder' => '',
    'value' => '',
    'error' => null,
    'required' => false,
    'helpText' => null,
    'size' => 'md',
])

@php
    $inputId = $name . '_' . uniqid();

    $sizeClass = match($size) {
        'xs' => 'input-xs',
        'sm' => 'input-sm',
        'lg' => 'input-lg',
        default => 'input-md',
    };

    $fileSizeClass = match($size) {
        'xs' => 'file-input-xs',
        'sm' => 'file-input-sm',
        'lg' => 'file-input-lg',
        default => 'file-input-md',
    };
@endphp

@if($type === 'hidden')
    <input
        type="hidden"
        id="{{ $inputId }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        {{ $attributes }}
    />
@else
    <div class="w-full min-w-[150px]">
        @if($label)
            <label class="label" for="{{ $inputId }}">
                <span class="label-text">
                    {{ $label }}
                    @if($required)
                        <span class="text-error">*</span>
                    @endif
                </span>
            </label>

            @if($helpText && !$error)
                <label class="label">
                    <span class="text-wrap label-text-alt text-base-content/70">{{ $helpText }}</span>
                </label>
            @endif
        @endif

        @if($type === 'password')
            <div class="relative">
                <input
                    type="password"
                    id="{{ $inputId }}"
                    name="{{ $name }}"
                    placeholder="{{ $placeholder }}"
                    value="{{ old($name, $value) }}"
                    {{ $attributes->merge([
                        'class' => 'input input-bordered w-full pr-12 ' . $sizeClass . ($error ? ' input-error' : '')
                    ]) }}
                    {{ $required ? 'required' : '' }}
                />

                <button
                    type="button"
                    onclick="togglePassword('{{ $inputId }}')"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-base-content/60 hover:text-base-content transition-colors"
                >
                    <svg id="{{ $inputId }}_icon_hide" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg id="{{ $inputId }}_icon_show" class="w-5 h-5 hidden" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>
        @elseif ($type === 'file')
            <div class="flex items-center gap-2">
                <input
                    type="{{ $type }}"
                    id="{{ $inputId }}"
                    name="{{ $name }}"
                    placeholder="{{ $placeholder }}"
                    {{ $attributes->merge([
                        'class' => 'file-input w-full ' . $fileSizeClass . ($error ? ' file-input-error' : '')
                    ]) }}
                    {{ $required ? 'required' : '' }}
                />
                @if($value)
                    <a href="{{ Storage::disk('public')->url($value) }}" target="_blank" class="btn btn-outline">Lihat File</a>
                @endif
            </div>
        @else
            <div class="flex items-center gap-2">
                <input
                    type="{{ $type }}"
                    id="{{ $inputId }}"
                    name="{{ $name }}"
                    placeholder="{{ $placeholder }}"
                    value="{{ old($name, $value) }}"
                    {{ $attributes->merge([
                        'class' => 'input input-bordered w-full ' . $sizeClass . ($error ? ' input-error' : '')
                    ]) }}
                    {{ $required ? 'required' : '' }}
                />
            </div>
        @endif

        @if($error)
            <label class="label">
                <span class="label-text-alt text-error">{{ $error }}</span>
            </label>
        @endif

        @error($name)
            <label class="label">
                <span class="label-text-alt text-error">{{ $message }}</span>
            </label>
        @enderror
    </div>
@endif

@if($type === 'password')
    @once
        <script>
            function togglePassword(inputId) {
                const input = document.getElementById(inputId);
                const iconHide = document.getElementById(inputId + '_icon_hide');
                const iconShow = document.getElementById(inputId + '_icon_show');

                if (input.type === 'password') {
                    input.type = 'text';
                    iconHide.classList.add('hidden');
                    iconShow.classList.remove('hidden');
                } else {
                    input.type = 'password';
                    iconHide.classList.remove('hidden');
                    iconShow.classList.add('hidden');
                }
            }
        </script>
    @endonce
@endif
