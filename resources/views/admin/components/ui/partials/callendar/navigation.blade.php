@php
    $navigationCardClass = $isSlim ? 'mb-3' : 'mb-6';
    $navigationButtonClass = $isSlim ? 'btn-xs' : 'btn-sm';
    $navigationTitleClass = $isSlim ? 'text-base sm:text-lg' : 'text-lg';
    $navigationIconClass = $isSlim ? 'h-4 w-4' : 'h-5 w-5';
    $legendWrapperClass = $isSlim
        ? 'mt-2 gap-4 text-[11px] sm:text-xs'
        : 'mt-3 gap-8 text-xs md:text-sm';
    $legendDotClass = $isSlim ? 'h-2.5 w-2.5' : 'h-3 w-3';
@endphp

{{-- Navigation --}}
<x-ui.card class="{{ $navigationCardClass }}">
    <div class="flex items-center justify-between">
        <x-ui.button type="ghost" :isSubmit="false" class="{{ $navigationButtonClass }}" @click="prevMonth()">
            <svg xmlns="http://www.w3.org/2000/svg" class="{{ $navigationIconClass }}" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </x-ui.button>
        <h3 class="{{ $navigationTitleClass }} font-bold" x-text="monthYear"></h3>
        <x-ui.button type="ghost" :isSubmit="false" class="{{ $navigationButtonClass }}" @click="nextMonth()">
            <svg xmlns="http://www.w3.org/2000/svg" class="{{ $navigationIconClass }}" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </x-ui.button>
    </div>

    {{-- Legend --}}
    <div class="flex items-center justify-center flex-wrap {{ $legendWrapperClass }}">
        @if ($showScheduleLegend)
            <div class="flex items-center gap-1.5">
                <span class="{{ $legendDotClass }} rounded-full bg-primary"></span>
                <span>Jadwal Kuliah</span>
            </div>
        @endif
        @if ($showDeadlineLegend)
            <div class="flex items-center gap-1.5">
                <span class="{{ $legendDotClass }} rounded-full bg-error"></span>
                <span>Deadline Tugas</span>
            </div>
        @endif
        @if ($showCustomLegend)
            <div class="flex items-center gap-1.5">
                <span class="{{ $legendDotClass }} rounded-full bg-primary"></span>
                <span class="{{ $legendDotClass }} rounded-full bg-success"></span>
                <span class="{{ $legendDotClass }} rounded-full bg-info"></span>
                <span>{{ $customLegendLabel }}</span>
            </div>
        @endif
        <div class="flex items-center gap-1.5">
            <span class="{{ $legendDotClass }} rounded-full bg-base-300"></span>
            <span>Hari Ini</span>
        </div>
    </div>
</x-ui.card>
