<div class="space-y-4">
    <div class="grid gap-4 md:grid-cols-2">
        <div class="space-y-2">
            <label class="text-sm font-medium text-primary">Tanggal Kunjungan</label>
            <input type="date" name="reservation_date" x-model="selectedDate" @change="loadAvailability"
                min="{{ now()->toDateString() }}"
                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-700" required>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-primary">Jumlah Tamu</label>
            <input type="number" name="guest_count" min="1" max="12" x-model.number="guestCount"
                @input.debounce.350ms="loadAvailability"
                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-700" required>
        </div>
    </div>

    <input type="hidden" name="start_time" :value="selectedTime">

    <div class="rounded-md border border-gray-100 bg-gray-50 p-4 text-sm font-light text-gray-600">
        <p class="font-semibold text-primary" x-text="selectedDateLabel"></p>
        <p class="mt-1" x-show="message" x-text="message"></p>
        <p class="mt-1" x-show="!message">Pilih salah satu slot yang masih tersedia untuk melanjutkan reservasi.</p>
    </div>

    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        <template x-for="slot in slots" :key="slot.time">
            <button type="button" @click="selectTime(slot.time)" :disabled="!slot.available"
                class="rounded-md border px-4 py-4 text-left transition cursor-pointer"
                :class="slot.available
                    ? (selectedTime === slot.time
                        ? 'border-primary bg-primary text-white'
                        : 'border-gray-200 hover:border-primary hover:bg-primary/5')
                    : 'cursor-not-allowed border-gray-100 bg-gray-100 text-gray-400'">
                <span class="block text-sm font-semibold" x-text="slot.label"></span>
                <span class="mt-1 block text-xs" x-text="slot.name"></span>
                <span class="mt-2 block text-xs" x-text="slot.available ? slot.available_label : 'Slot penuh'"></span>
            </button>
        </template>
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

@push('scripts')
    <script>
        if (!window.bookingReservationForm) {
            window.bookingReservationForm = function bookingReservationForm(config) {
                return {
                    availabilityUrl: config.availabilityUrl,
                    selectedDate: config.initialDate,
                    selectedTime: config.initialTime,
                    selectedDateLabel: '',
                    guestCount: Number(config.initialGuestCount || 2),
                    slots: Array.isArray(config.initialSlots) ? config.initialSlots : [],
                    message: config.initialMessage || null,
                    loading: false,

                    init() {
                        this.syncDateLabel();

                        if (!this.slots.length) {
                            this.loadAvailability();
                        }
                    },

                    syncDateLabel() {
                        if (!this.selectedDate) {
                            this.selectedDateLabel = 'Pilih tanggal reservasi';
                            return;
                        }

                        const date = new Date(`${this.selectedDate}T00:00:00`);

                        if (Number.isNaN(date.getTime())) {
                            this.selectedDateLabel = this.selectedDate;
                            return;
                        }

                        this.selectedDateLabel = new Intl.DateTimeFormat('id-ID', {
                            weekday: 'long',
                            day: '2-digit',
                            month: 'long',
                            year: 'numeric',
                        }).format(date);
                    },

                    selectTime(time) {
                        this.selectedTime = time;
                    },

                    async loadAvailability() {
                        if (!this.selectedDate || !this.guestCount) {
                            return;
                        }

                        this.loading = true;
                        this.syncDateLabel();

                        const currentTime = this.selectedTime;

                        try {
                            const url = new URL(this.availabilityUrl, window.location.origin);
                            url.searchParams.set('date', this.selectedDate);
                            url.searchParams.set('guest_count', this.guestCount);

                            const response = await fetch(url, {
                                headers: {
                                    Accept: 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                            });

                            const data = await response.json();

                            this.slots = Array.isArray(data.slots) ? data.slots : [];
                            this.message = data.message || null;
                            this.selectedDate = data.date || this.selectedDate;
                            this.syncDateLabel();

                            const stillAvailable = this.slots.find((slot) => slot.time === currentTime && slot.available);
                            const firstAvailable = this.slots.find((slot) => slot.available);

                            this.selectedTime = stillAvailable ? stillAvailable.time : (firstAvailable ? firstAvailable.time : '');
                        } catch (error) {
                            this.message = 'Gagal memuat ketersediaan slot. Coba lagi beberapa saat lagi.';
                            this.selectedTime = '';
                        } finally {
                            this.loading = false;
                        }
                    },
                };
            };
        }
    </script>
@endpush
