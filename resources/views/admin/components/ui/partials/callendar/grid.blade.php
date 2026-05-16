@php
    $gridDayHeaderClass = $isSlim
        ? 'text-center text-[11px] sm:text-xs font-semibold text-base-content/60 py-0.5 sm:py-1'
        : 'text-center text-xs sm:text-sm font-semibold text-base-content/60 py-1 sm:py-2';
    $gridTextClass = $isSlim ? 'text-[10px] sm:text-[11px]' : 'text-[11px] sm:text-xs';
    $gridCellClass = $isSlim
        ? 'bg-base-100 min-h-[52px] sm:min-h-[72px] p-1 relative group border-r border-b border-base-200 focus-within:z-30 select-none'
        : 'bg-base-100 min-h-[60px] sm:min-h-[100px] p-1 sm:p-1.5 relative group border-r border-b border-base-200 focus-within:z-30 select-none';
    $gridDayBadgeClass = $isSlim
        ? 'font-medium mb-0.5 -mx-1 flex items-center gap-1 select-none'
        : 'font-medium mb-1 -mx-1 flex items-center gap-1 select-none';
    $gridSpanClass = $isSlim
        ? 'h-3.5 flex items-center text-[8px] font-medium -mx-1 px-1 overflow-visible truncate select-none'
        : 'h-4 flex items-center text-[9px] font-medium -mx-1 sm:-mx-1.5 px-1 overflow-visible truncate select-none';
    $gridEventClass = $isSlim
        ? 'text-[8px] sm:text-[9px] leading-tight px-1 py-0.5 rounded truncate cursor-default select-none'
        : 'text-[9px] sm:text-[10px] leading-tight px-1 py-0.5 rounded truncate cursor-default select-none';
@endphp

{{-- Calendar Grid --}}
<x-ui.card class="select-none">
    {{-- Day Headers --}}
    <div class="mb-1 grid select-none grid-cols-7 gap-px sm:mb-2">
        <template x-for="day in ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']" :key="day">
            <div class="{{ $gridDayHeaderClass }}" x-text="day">
            </div>
        </template>
    </div>

    {{-- Date Grid --}}
    <div class="grid select-none grid-cols-7 gap-px overflow-visible rounded-lg border border-base-200 {{ $gridTextClass }}">
        <template x-for="(cell, index) in calendarCells" :key="index">
            <div class="{{ $gridCellClass }}"
                :class="{
                    'bg-primary/5': cell.isToday && !isInDragSelection(index) && !isSelectedCell(cell),
                    'bg-primary/10 ring-2 ring-inset ring-primary/45 shadow-sm shadow-primary/10': isSelectedCell(cell),
                    'opacity-40': !cell.currentMonth,
                    'cursor-pointer': cell.currentMonth && (interactive || canDispatchDateClick(cell)),
                    'cursor-default': !cell.currentMonth || (!interactive && !canDispatchDateClick(cell)),
                    'bg-success/10 ring-1 ring-inset ring-success/40': isInDragSelection(index) && isDragging && cell
                        .currentMonth,
                }"
                @mousedown.prevent="if (interactive) onCellMouseDown(index, cell)"
                @selectstart.prevent
                @mouseenter="if (interactive) onCellMouseEnter(index, cell)"
                @click="handlePassiveCellClick(cell)">

                <div class="{{ $gridDayBadgeClass }} ml-1"
                    :class="cell.isToday || isSelectedCell(cell) ? 'text-primary font-bold' : 'text-base-content/70'">
                    <div :class="cell.isToday ? 'badge badge-primary badge-xs' : isSelectedCell(cell) ? '' : ''">
                        <span x-text="cell.day"></span>
                        <span x-show="cell.isToday" class="hidden sm:block">Hari Ini</span>
                    </div>
                </div>

                {{-- Multi-day span bars --}}
                <template x-for="(bar, bi) in (cell.spanBars || [])" :key="bar.eventId || bi">
                    <div class="relative mb-0.5" @mouseenter="bar._hover = true" @mouseleave="bar._hover = false">
                        <div class="{{ $gridSpanClass }}"
                            :class="[
                                getEventPillClass(bar.color),
                                bar.isStart ? 'rounded-l-full' : '',
                                bar.isEnd ? 'rounded-r-full' : ''
                            ]">
                            <span x-show="bar.showLabel" class="truncate block w-full px-0.5 font-semibold"
                                x-text="bar.title"></span>
                        </div>

                        <div x-cloak x-show="bar._hover" x-transition.opacity
                            class="pointer-events-none absolute left-0 top-full mt-1 z-50 w-48 rounded-md bg-neutral px-2 py-1.5 text-[10px] text-neutral-content shadow-lg">
                            <div class="font-semibold" x-text="bar.title"></div>
                            <div x-show="bar.location" class="opacity-80" x-text="'Lokasi: ' + bar.location"></div>
                            <div x-show="bar.start" class="opacity-80" x-text="'Mulai: ' + bar.start"></div>
                            <div x-show="bar.end" class="opacity-80" x-text="'Selesai: ' + bar.end"></div>
                        </div>
                    </div>
                </template>

                {{-- Events --}}
                <div class="space-y-0.5">
                    <template x-for="(event, ei) in cell.events" :key="event.eventId || ei">
                        <div class="relative" @mouseenter="event._hover = true" @mouseleave="event._hover = false">
                            <div class="{{ $gridEventClass }}"
                                :class="[
                                    event.type === 'jadwal' ?
                                        'bg-primary/15 text-primary' :
                                        event.type === 'custom' ?
                                        getEventPillClass(event.color) :
                                        'bg-error/15 text-error',
                                    canDispatchEventClick(event) ?
                                        'cursor-pointer hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-primary/25' :
                                        'cursor-default'
                                ]"
                                @click.stop="dispatchExternalEventClick(event)"
                                x-text="event.title">
                            </div>

                            <div x-cloak x-show="event._hover" x-transition.opacity
                                class="pointer-events-none absolute left-0 top-full mt-1 z-50 w-52 rounded-md bg-neutral px-2 py-1.5 text-[10px] text-neutral-content shadow-lg">
                                <div class="font-semibold" x-text="event.title"></div>
                                <div x-show="event.jam_mulai || event.jam_selesai" class="opacity-80"
                                    x-text="`${event.jam_mulai || '-'} - ${event.jam_selesai || '-'}`">
                                </div>
                                <div x-show="event.ruangan" class="opacity-80" x-text="'Ruangan: ' + event.ruangan">
                                </div>
                                <div x-show="event.location" class="opacity-80" x-text="'Lokasi: ' + event.location">
                                </div>
                                <div x-show="event.description" class="opacity-80" x-text="event.description"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</x-ui.card>
