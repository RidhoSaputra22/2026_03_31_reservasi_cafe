{{--
    Reusable Checkbox Component

    @param string $name - Input name (use array syntax for multiple: name="items[]")
    @param string $label - Label text for the group
    @param array $options - Array of options [['value' => 'val', 'label' => 'Label']]
    @param array $checked - Array of checked values
    @param bool $required - Required field
    @param string $layout - Layout direction: 'horizontal' or 'vertical'
    @param string $helpText - Helper text below checkbox group
    @param bool $single - If true, renders a single checkbox instead of group
--}}

@props([
    'name' => null,
    'id' => null,
    'label' => null,
    'options' => [],
    'checked' => [],
    'required' => false,
    'layout' => 'horizontal',
    'helpText' => null,
    'single' => true,
    'singleLabel' => null,
    'error' => null,
    'bare' => false,
    'unstyled' => false,
])

@php
    $singleChecked = $name ? old($name, $checked) : $checked;
    $checkedValues = $name ? old($name, (array) $checked) : (array) $checked;
    $inputId = $id ?: ($bare ? null : ($name ? str_replace(['[', ']'], '_', $name) . '_' . uniqid() : 'checkbox_' . uniqid()));
    $defaultClass = $unstyled ? '' : 'checkbox checkbox-primary';
@endphp

@if($single && $bare)
    <input
        type="checkbox"
        @if($inputId) id="{{ $inputId }}" @endif
        @if($name) name="{{ $name }}" @endif
        value="1"
        @checked($singleChecked)
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => $defaultClass]) }}
    />
@else
    <div class="form-control w-full">
        @if($label)
            <label class="label">
                <span class="label-text">
                    {{ $label }}
                    @if($required)
                        <span class="text-error">*</span>
                    @endif
                </span>
            </label>
        @endif

        @if($single)
            <label class="flex cursor-pointer items-center gap-2" @if($inputId) for="{{ $inputId }}" @endif>
                <input
                    type="checkbox"
                    @if($inputId) id="{{ $inputId }}" @endif
                    @if($name) name="{{ $name }}" @endif
                    value="1"
                    @checked($singleChecked)
                    {{ $required ? 'required' : '' }}
                    {{ $attributes->merge(['class' => $defaultClass]) }}
                />

                @if(!$slot->isEmpty())
                    <div class="min-w-0 flex-1">
                        {{ $slot }}
                    </div>
                @elseif($singleLabel || ($options[0]['label'] ?? null))
                    <span class="label-text">{{ $singleLabel ?? ($options[0]['label'] ?? '') }}</span>
                @endif
            </label>
        @else
            <div class="flex {{ $layout === 'vertical' ? 'flex-col' : 'flex-wrap' }} gap-4">
                @foreach($options as $option)
                    @php
                        $isDisabled = isset($option['disabled']) && $option['disabled'];
                        $optionId = ($id ?: ($name ? str_replace(['[', ']'], '_', $name) : 'checkbox')) . '_' . $loop->index;
                    @endphp
                    <label class="flex cursor-pointer items-center gap-2 {{ $isDisabled ? 'opacity-50' : '' }}" for="{{ $optionId }}">
                        <input
                            type="checkbox"
                            id="{{ $optionId }}"
                            @if($name) name="{{ $name }}" @endif
                            value="{{ $option['value'] }}"
                            {{ in_array($option['value'], (array) $checkedValues) ? 'checked' : '' }}
                            {{ $required ? 'required' : '' }}
                            {{ $isDisabled ? 'disabled' : '' }}
                            {{ $attributes->merge(['class' => $defaultClass]) }}
                        />
                        <span>{{ $option['label'] }}</span>
                    </label>
                @endforeach
            </div>
        @endif

        @if($helpText)
            <label class="label">
                <span class="text-wrap label-text-alt text-base-content/70">{{ $helpText }}</span>
            </label>
        @endif

        @if($error)
            <label class="label">
                <span class="label-text-alt text-error">{{ $error }}</span>
            </label>
        @endif

        @if($name)
            @error($name)
                <label class="label">
                    <span class="label-text-alt text-error">{{ $message }}</span>
                </label>
            @enderror
        @endif
    </div>
@endif
