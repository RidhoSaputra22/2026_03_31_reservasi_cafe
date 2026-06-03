<div x-cloak x-show="pickerOpen" class="fixed inset-0 z-[95]" @keydown.escape.window="closePicker()">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" @click="closePicker()"></div>

    <div class="absolute inset-4 md:bottom-8 md:right-8 md:left-auto md:top-auto md:w-[32rem] lg:w-[48rem]">
        <div class="overflow-hidden rounded-[1.75rem] bg-white shadow-2xl">
            <div class="space-y-6 p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-2">
                        <h3 class="text-2xl font-semibold text-gray-900">Pilih Tanggal & Waktu Reservasi</h3>
                        <p class="text-sm font-light leading-6 text-gray-600">
                            Atur kunjunganmu dengan memilih tanggal yang nyaman, lalu ambil slot aktif yang masih tersedia.
                        </p>
                    </div>

                    <button type="button" @click="closePicker()"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 text-gray-500 transition hover:bg-gray-50 hover:text-gray-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M6 18 18 6" />
                        </svg>
                    </button>
                </div>

                <div x-show="pickerStep === 'calendar'" class="space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <button type="button" @click="goToPreviousMonth()"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 text-gray-600 transition hover:border-primary hover:text-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                                </svg>
                            </button>
                            <button type="button" @click="goToNextMonth()"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 text-gray-600 transition hover:border-primary hover:text-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                            <button type="button" @click="jumpToToday()"
                                class="rounded-xl border border-gray-200 px-4 py-2 font-medium text-gray-700 transition hover:border-primary hover:text-primary">
                                Hari ini
                            </button>
                        </div>

                        <div class="text-right">
                            <p class="text-4xl font-semibold text-gray-900" x-text="calendarTitle()"></p>
                            <p class="mt-1 text-sm font-light text-gray-600">
                                Jumlah tamu: <span class="font-semibold text-primary" x-text="guestCount"></span>
                            </p>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-gray-200">
                        <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50">
                            <template x-for="weekday in weekdayLabels" :key="weekday">
                                <div class="px-2 py-3 text-center text-sm font-semibold text-gray-700" x-text="weekday"></div>
                            </template>
                        </div>

                        <div class="grid grid-cols-7">
                            <template x-for="day in calendarDays()" :key="day.key">
                                <button type="button" @click="selectCalendarDay(day)" :disabled="day.disabled"
                                    class="relative min-h-24 border-r border-b border-gray-200 bg-white px-3 py-3 text-left text-gray-800 transition last:border-r-0 hover:bg-primary/5 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-300"
                                    :class="{
                                        'bg-primary text-white hover:bg-primary': day.date === (pickerDate || selectedDate),
                                        'text-gray-300': !day.currentMonth && day.date !== (pickerDate || selectedDate),
                                        'ring-1 ring-inset ring-primary/25': day.isToday && day.date !== (pickerDate || selectedDate),
                                    }">
                                    <span class="text-base font-semibold" x-text="day.day"></span>
                                    <span x-show="day.isToday"
                                        class="absolute bottom-3 left-3 rounded-full px-2 py-1 text-[10px] font-bold uppercase tracking-[0.14em]"
                                        :class="day.date === (pickerDate || selectedDate) ? 'bg-white/15 text-white' : 'bg-primary/10 text-primary'">
                                        Hari ini
                                    </span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <div x-show="pickerStep === 'slots'" class="space-y-6">
                    <div class="space-y-2">
                        <p class="text-lg font-semibold text-gray-900">
                            Pilih jam untuk <span x-text="pickerDateLabel()"></span>
                        </p>
                        <p class="text-sm font-light leading-6 text-gray-600"
                            x-text="pickerMessage || 'Pilih salah satu slot aktif yang masih tersedia.'"></p>
                    </div>

                    <div x-show="loading" class="rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-4 text-sm text-gray-600">
                        Memuat slot yang tersedia...
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <template x-for="slot in pickerSlots" :key="slot.time">
                            <button type="button" @click="selectPickerTime(slot.time)" :disabled="!slot.available || loading"
                                class="rounded-xl border px-4 py-4 text-left transition"
                                :class="!slot.available
                                    ? 'cursor-not-allowed border-gray-100 bg-gray-100 text-gray-400'
                                    : (slot.time === pickerTime
                                        ? 'border-primary bg-primary text-white'
                                        : 'border-gray-200 hover:border-primary hover:bg-primary/5')">
                                <span class="block text-sm font-semibold" x-text="slot.label"></span>
                                <span class="mt-1 block text-xs" x-text="slot.name"></span>
                                <span class="mt-2 block text-xs" x-text="slot.available ? slot.available_label : 'Slot penuh'"></span>
                            </button>
                        </template>
                    </div>

                    <div x-show="!loading && pickerSlots.length === 0"
                        class="rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-4 text-sm text-gray-600">
                        Belum ada slot aktif pada tanggal ini. Coba pilih tanggal lain.
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <button type="button" @click="pickerStep = 'calendar'"
                            class="text-sm font-semibold text-primary transition hover:text-primary/80">
                            &larr; Kembali
                        </button>

                        <button type="button" @click="applyPickerSelection()" :disabled="!pickerTime || loading"
                            class="rounded-md bg-primary px-4 py-3 text-sm font-semibold text-white transition hover:bg-primary/90 disabled:cursor-not-allowed disabled:bg-primary/50">
                            Lanjutkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
