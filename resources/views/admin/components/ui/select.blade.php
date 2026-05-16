@props([
    'name' => null,
    'label' => null,
    'options' => [],
    'value' => null,
    'selected' => null,
    'placeholder' => 'Pilih...',
    'error' => null,
    'required' => false,
    'searchable' => null,
    'searchPlaceholder' => 'Cari...',
    'size' => 'md',
])

@php
    $selectedValue = old($name, $value ?? $selected);

    $normalizedOptions = [];
    foreach ($options as $key => $option) {
        if (is_array($option) && isset($option['value']) && isset($option['label'])) {
            $normalizedOptions[] = [
                'value' => $option['value'],
                'label' => $option['label'],
            ];
        } else {
            $normalizedOptions[] = [
                'value' => $key,
                'label' => $option,
            ];
        }
    }

    $xModel = $attributes->get('x-model');

    $selectSizeClass = match($size) {
        'xs' => 'select-xs',
        'sm' => 'select-sm',
        'lg' => 'select-lg',
        default => 'select-md',
    };

    $searchInputSizeClass = match($size) {
        'xs' => 'input-xs',
        'sm' => 'input-sm',
        'lg' => 'input-lg',
        default => 'input-md',
    };
@endphp

<div class="w-full ">
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

    @if($searchable)
        <div
            x-data="searchableSelect({
                name: @js($name),
                placeholder: @js($placeholder),
                selectedValue: @js($selectedValue),
                options: @js($normalizedOptions),
                modelName: @js($xModel),
            })"
            x-init="init()"
            @click.outside="isOpen = false"
            class="relative"
        >
            <input
                type="hidden"
                name="{{ $name }}"
                x-model="selectedValue"
                {{ $required ? 'required' : '' }}
            >

            <button
                type="button"
                @click="isOpen = !isOpen"
                {{ $attributes->except('x-model')->merge([
                    'class' => 'select select-bordered w-full flex items-center justify-between ' . $selectSizeClass . ($error ? ' select-error' : '')
                ]) }}
            >
                <span
                    x-text="selectedLabel || placeholder"
                    :class="!selectedLabel ? 'text-base-content/40' : ''"
                ></span>


            </button>

            <div
                x-cloak
                x-show="isOpen"
                class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg"
            >
                <div class="p-2 border-b border-base-300">
                    <div class="relative">
                        <input
                            type="text"
                            x-model="searchTerm"
                            placeholder="{{ $searchPlaceholder }}"
                            class="input input-bordered w-full pr-8 {{ $searchInputSizeClass }}"
                            @click.stop
                        >
                        <x-heroicon-s-magnifying-glass class="w-4 h-4 absolute right-2 top-1/2 -translate-y-1/2 text-base-content/60" />
                    </div>
                </div>

                <div class="max-h-60 overflow-y-auto">
                    @if($placeholder && !$required)
                        <button
                            type="button"
                            @click="selectOption('', placeholder)"
                            class="w-full text-left px-4 py-2 hover:bg-base-200 text-base-content/40"
                            :class="selectedValue === '' ? 'bg-primary/10' : ''"
                        >
                            {{ $placeholder }}
                        </button>
                    @endif

                    <template x-for="option in filteredOptions" :key="option.value">
                        <button
                            type="button"
                            @click="selectOption(option.value, option.label)"
                            class="w-full text-left px-4 py-2 hover:bg-base-200 transition-colors"
                            :class="selectedValue == option.value ? 'bg-primary/10 font-medium' : ''"
                        >
                            <span x-text="option.label"></span>
                        </button>
                    </template>

                    <div x-show="filteredOptions.length === 0" class="px-4 py-3 text-center text-base-content/60 text-sm">
                        Tidak ada hasil
                    </div>
                </div>
            </div>
        </div>
    @else
        <select
            id="{{ $name }}"
            name="{{ $name }}"
            {{ $attributes->merge([
                'class' => 'select select-bordered w-full ' . $selectSizeClass . ($error ? ' select-error' : '')
            ]) }}
            {{ $required ? 'required' : '' }}
        >
            @if($placeholder)
                <option value="" disabled {{ !$selectedValue ? 'selected' : '' }}>
                    {{ $placeholder }}
                </option>
            @endif

            @if(!$slot->isEmpty())
                {{ $slot }}
            @else
                @foreach($normalizedOptions as $option)
                    <option value="{{ $option['value'] }}" {{ $selectedValue == $option['value'] ? 'selected' : '' }}>
                        {{ $option['label'] }}
                    </option>
                @endforeach
            @endif
        </select>
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

@once
    @push('scripts')
        <script>
            function searchableSelect(config) {
                return {
                    isOpen: false,
                    searchTerm: '',
                    selectedValue: config.selectedValue ?? '',
                    selectedLabel: '',
                    placeholder: config.placeholder ?? 'Pilih...',
                    options: Array.isArray(config.options) ? config.options : [],
                    modelName: config.modelName ?? null,
                    name: config.name ?? null,

                    get filteredOptions() {
                        if (!this.searchTerm) return this.options;

                        return this.options.filter(option =>
                            String(option.label).toLowerCase().includes(this.searchTerm.toLowerCase())
                        );
                    },

                    selectOption(value, label) {
                        this.selectedValue = value;
                        this.selectedLabel = label;
                        this.isOpen = false;
                        this.searchTerm = '';

                        if (this.modelName && this.$root && this.$root.__x) {
                            this.$root.__x.$data[this.modelName] = value;
                        }

                        this.$dispatch('select-change', {
                            name: this.name,
                            value: value
                        });
                    },

                    init() {
                        const selected = this.options.find(opt => String(opt.value) == String(this.selectedValue));
                        if (selected) {
                            this.selectedLabel = selected.label;
                        }

                        if (!this.selectedLabel && this.selectedValue === '') {
                            this.selectedLabel = '';
                        }
                    }
                }
            }
        </script>
    @endpush
@endonce
