{{--
    Reusable Textarea Component

    @param string $name - Textarea name
    @param string $label - Label text
    @param string $placeholder - Placeholder text
    @param string $value - Current value
    @param string $error - Error message
    @param bool $required - Required field
    @param int $rows - Number of rows
--}}

@props([
'name' => 'null',
'label' => null,
'placeholder' => '',
'value' => '',
'error' => null,
'required' => false,
'rows' => 4,
'class' => '',
])

<div class="form-control w-full {{ $class }}">
    @if($label)
    <label class="label" for="{{ $name }}">
        <span class="label-text">
            {{ $label }}
            @if($required)
            <span class="text-error">*</span>
            @endif
        </span>
    </label>
    @endif

    <textarea id="{{ $name }}" name="{{ $name }}" placeholder="{{ $placeholder }}" rows="{{ $rows }}"
        {{ $attributes->merge(['class' => 'textarea textarea-bordered w-full' . ($error ? ' textarea-error' : '')]) }}
        {{ $required ? 'required' : '' }}>{{ old($name, $value) }}</textarea>

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
