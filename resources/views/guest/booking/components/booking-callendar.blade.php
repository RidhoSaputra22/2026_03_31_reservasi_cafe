<div class="space-y-4">
    <div class="grid gap-4 md:grid-cols-2">
        <div class="space-y-2">
            <label class="text-sm font-medium text-primary">Pilih Tanggal & Waktu Reservasi</label>
            <button type="button" @click="openPicker()"
                class="flex w-full items-center justify-between rounded-xl border border-dashed border-gray-300 px-4 py-4 text-left transition hover:border-primary hover:bg-primary/5">
                <div class="min-w-0">
                    <span class="block text-xs font-semibold uppercase tracking-[0.2em] text-primary/60">Klik untuk memilih</span>
                    <span class="mt-2 block truncate text-sm font-semibold text-gray-700"
                        x-text="selectedDateLabel || 'Pilih tanggal reservasi'"></span>
                    <span class="mt-1 block text-xs text-gray-500"
                        x-text="selectedTime ? selectedSlotLabel() : 'Pilih tanggal terlebih dahulu lalu ambil slot aktif.'"></span>
                </div>

                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-primary/20 text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8.25 7.5V6a3.75 3.75 0 1 1 7.5 0v1.5m-9 0h10.5A2.25 2.25 0 0 1 19.5 9.75v7.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 17.25v-7.5A2.25 2.25 0 0 1 6.75 7.5Z" />
                    </svg>
                </span>
            </button>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-primary">Jumlah Tamu</label>
            <input type="number" name="guest_count" min="1" max="{{ $maxGuestCount }}" x-model.number="guestCount"
                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-700" required>
            <p class="text-xs font-light text-gray-500">
                Maksimal <span class="font-semibold text-primary">{{ $maxGuestCount }} tamu</span> mengikuti kapasitas meja aktif.
            </p>
        </div>
    </div>

    <input type="hidden" name="reservation_date" :value="selectedDate">
    <input type="hidden" name="start_time" :value="selectedTime">

    <div class="rounded-md border border-gray-100 bg-gray-50 p-4 text-sm font-light text-gray-600">
        <p class="font-semibold text-primary" x-text="selectedDateLabel || 'Pilih tanggal reservasi'"></p>
        <p class="mt-1" x-show="committedMessage" x-text="committedMessage"></p>
        <p class="mt-1" x-show="!committedMessage">Pilih slot yang masih tersedia melalui kalender untuk melanjutkan reservasi.</p>
    </div>

    <div class="grid gap-3 text-sm font-light text-gray-600 md:grid-cols-2">
        <div class="rounded-md border border-gray-100 bg-gray-50 p-4">
            Durasi reservasi: <span class="font-semibold text-primary">{{ $package['duration'] }}</span>
        </div>
        <div class="rounded-md border border-gray-100 bg-gray-50 p-4">
            <span x-show="selectedTime">Jam terpilih: <span class="font-semibold text-primary" x-text="selectedTime"></span></span>
            <span x-show="!selectedTime">Belum ada slot yang dipilih.</span>
        </div>
    </div>
</div>
