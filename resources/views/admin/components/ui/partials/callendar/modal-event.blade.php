{{-- Modal Event --}}
<x-ui.modal id="modal-event" size="xl" :closeButton="true">
    <x-slot:title>
        <div class="flex  items-center gap-2 w-full">
            <x-heroicon-o-calendar-days class="h-8 w-8 text-primary/80" />
            <h3 class=" font-bold text-lg sm:text-xl ">Event <span x-text="modalDate"></span></h3>
            {{-- <div class="w-full"></div> --}}

        </div>
    </x-slot:title>
    @if ($allowEventCrud)
        <x-slot:modal-actions position="bottom-right">
            <x-ui.button type="success" size="sm" :isSubmit="false" class="gap-1" @click="openCreateEvent()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Event
                </x-ui.button>
        </x-slot:modal-actions>
    @endif

    <div class="flex items-center justify-between mb-3">
        <p x-show="dragRangeLabel" class="text-xs text-base-content/50" x-text="dragRangeLabel"></p>
        <span x-show="!dragRangeLabel"></span>

    </div>

    <div x-show="modalEvents.length === 0" class="text-center text-base-content/50 text-xs py-4">
        Tidak ada event
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3  max-h-[28rem] overflow-y-auto ">
        <template x-for="(event, idx) in modalEvents" :key="idx">
            <div class=" card border p-3"
                :class="event.type === 'deadline' ?
                    'border-error/30 bg-error/5' :
                    event.type === 'custom' ?
                    getEventSoftClass(event.color) :
                    'border-primary/30 bg-primary/5'">

                {{-- Event header --}}
                <div class="flex items-start gap-2 mb-2">
                    <span class="mt-1 inline-block w-2 h-2 rounded-full flex-shrink-0"
                        :class="event.type === 'deadline' ?
                            'bg-error' :
                            event.type === 'custom' ?
                            getEventDotClass(event.color) :
                            'bg-primary'"></span>

                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold leading-snug"
                            :class="event.type === 'deadline' ?
                                'text-error' :
                                event.type === 'custom' ?
                                getEventTextClass(event.color) :
                                'text-primary'"
                            x-text="event.title"></p>

                        <span class="text-[10px] font-medium uppercase tracking-wide"
                            :class="event.type === 'deadline' ?
                                'text-error/60' :
                                event.type === 'custom' ?
                                getEventMutedTextClass(event.color) :
                                'text-primary/60'"
                            x-text="event.type === 'deadline'
                                ? 'Deadline Tugas'
                                : event.type === 'custom'
                                    ? customEventLabel
                                    : 'Jadwal Kuliah'"></span>
                    </div>

                    {{-- Edit / Delete buttons for custom events --}}
                    @if ($allowEventCrud)
                        <template x-if="event.type === 'custom'">
                        <div class="flex items-center gap-1 ml-auto flex-shrink-0">
                            <x-ui.button type="ghost" size="xs" :isSubmit="false" ::class="getEventTextClass(event.color)"
                                @click.stop="openEditEvent(event)" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </x-ui.button>

                            <x-ui.button type="ghost" size="xs" :isSubmit="false" ::class="getEventTextClass(event.color)"
                                @click.stop="deleteEvent(event, idx)" title="Hapus">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </x-ui.button>
                        </div>
                        </template>
                    @endif
                </div>

                {{-- Jadwal details --}}
                <template x-if="event.type === 'jadwal'">
                    <div class="ml-4 space-y-1 text-xs text-base-content/70">
                        <div x-show="event.jam_mulai" class="flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0 text-primary/60"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span
                                x-text="event.jam_mulai + (event.jam_selesai ? ' – ' + event.jam_selesai : '')"></span>
                        </div>

                        <div x-show="event.ruangan" class="flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0 text-primary/60"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span x-text="event.ruangan"></span>
                        </div>
                    </div>
                </template>

                {{-- Deadline details --}}
                <template x-if="event.type === 'deadline'">
                    <div class="ml-4 space-y-1 text-xs text-base-content/70">
                        <div x-show="event.mata_kuliah" class="flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0 text-error/60"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span x-text="event.mata_kuliah"></span>
                        </div>

                        <div x-show="event.status" class="flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0 text-error/60"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span x-text="event.status"></span>
                        </div>

                        <div x-show="event.progress !== null && event.progress !== undefined" class="space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-3.5 h-3.5 flex-shrink-0 text-error/60" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    Progress
                                </span>
                                <span class="font-semibold" x-text="event.progress + '%'""></span>
                            </div>

                            <div class="w-full bg-base-300 rounded-full h-1.5">
                                <div class="bg-error h-1.5 rounded-full transition-all"
                                    :style="'width: ' + event.progress + '%'""></div>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Custom event details --}}
                <template x-if="event.type === 'custom'">
                    <div class="ml-4 space-y-1 text-xs" :class="getEventMutedTextClass(event.color)">
                        <div x-show="event.start" class="flex items-center gap-1.5 text-base-content/70">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor"
                                :class="getEventMutedTextClass(event.color)">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span x-text="event.start + (event.end ? ' – ' + event.end : '')"></span>
                        </div>

                        <div x-show="event.location" class="flex items-center gap-1.5 text-base-content/70">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor"
                                :class="getEventMutedTextClass(event.color)">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span x-text="event.location"></span>
                        </div>

                        <div x-show="event.description" class="flex items-start gap-1.5 text-base-content/70">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0 mt-0.5"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                :class="getEventMutedTextClass(event.color)">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h7" />
                            </svg>
                            <span x-text="event.description"></span>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>
</x-ui.modal>
