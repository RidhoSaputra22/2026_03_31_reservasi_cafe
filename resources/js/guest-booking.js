import Alpine from 'alpinejs';

function normalizeDate(value) {
    if (!value) {
        return '';
    }

    if (/^\d{4}-\d{2}-\d{2}$/.test(String(value))) {
        return String(value);
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    const year = date.getFullYear();
    const month = `${date.getMonth() + 1}`.padStart(2, '0');
    const day = `${date.getDate()}`.padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function normalizeTime(value) {
    if (!value) {
        return '';
    }

    const match = String(value).match(/^(\d{2}):(\d{2})(?::\d{2})?$/);

    if (!match) {
        return '';
    }

    return `${match[1]}:${match[2]}`;
}

function formatTimeInput(value) {
    if (typeof window.formatTime24hInput === 'function') {
        return window.formatTime24hInput(value);
    }

    return String(value ?? '').replace(/[^\d:]/g, '').slice(0, 5);
}

function isCompleteTime(value) {
    return normalizeTime(value) !== '';
}

function addHoursToTime(value, hours) {
    const normalized = normalizeTime(value);
    const numericHours = Number(hours || 0);

    if (!normalized || !Number.isFinite(numericHours)) {
        return '';
    }

    const [baseHours, baseMinutes] = normalized.split(':').map(Number);
    const totalMinutes = (baseHours * 60) + baseMinutes + (numericHours * 60);
    const normalizedMinutes = ((totalMinutes % 1440) + 1440) % 1440;
    const nextHours = `${Math.floor(normalizedMinutes / 60)}`.padStart(2, '0');
    const nextMinutes = `${normalizedMinutes % 60}`.padStart(2, '0');

    return `${nextHours}:${nextMinutes}`;
}

function durationLabel(value) {
    const numericValue = Number(value || 0);

    if (!Number.isFinite(numericValue) || numericValue <= 0) {
        return '1 jam';
    }

    return numericValue === 1 ? '1 jam' : `${numericValue} jam`;
}

document.addEventListener('alpine:init', () => {
    Alpine.data('bookingReservationForm', (config = {}) => ({
        availabilityUrl: config.availabilityUrl || '',
        today: normalizeDate(config.today) || normalizeDate(new Date()),
        maxGuestCount: Number(config.maxGuestCount || 12),
        durationOptions: Array.isArray(config.durationOptions) ? config.durationOptions : [1],
        reservationDate: normalizeDate(config.initialDate),
        startTime: normalizeTime(config.initialTime),
        durationHours: Number(config.initialDurationHours || 1),
        guestCount: Number(config.initialGuestCount || 2),
        availability: config.initialAvailability || {},
        estimatedPrice: Number(config.initialEstimatedPrice || 0),
        estimatedPriceLabel: config.initialEstimatedPriceLabel || '',
        confirmationModalOpen: false,
        submitting: false,
        loading: false,
        refreshDebounceId: null,

        init() {
            this.durationOptions = this.durationOptions
                .map((value) => Number(value))
                .filter((value) => Number.isInteger(value) && value > 0);

            if (this.durationOptions.length === 0) {
                this.durationOptions = [1];
            }

            this.durationHours = this.normalizeDuration(this.durationHours);
            this.guestCount = this.normalizeGuestCount(this.guestCount);

            this.$watch('reservationDate', () => this.queueRefresh());
            this.$watch('startTime', (value) => {
                const formattedValue = formatTimeInput(value);

                if (formattedValue !== value) {
                    this.startTime = formattedValue;

                    return;
                }

                this.queueRefresh();
            });
            this.$watch('durationHours', (value) => {
                this.durationHours = this.normalizeDuration(value);
                this.queueRefresh();
            });
            this.$watch('guestCount', (value) => {
                this.guestCount = this.normalizeGuestCount(value);
                this.queueRefresh();
            });
        },

        normalizeDuration(value) {
            const numericValue = Number(value || this.durationOptions[0]);

            return this.durationOptions.includes(numericValue)
                ? numericValue
                : this.durationOptions[0];
        },

        normalizeGuestCount(value) {
            return Math.max(1, Math.min(this.maxGuestCount, Number(value || 1)));
        },

        queueRefresh() {
            window.clearTimeout(this.refreshDebounceId);
            this.refreshDebounceId = window.setTimeout(() => {
                this.fetchAvailability();
            }, 350);
        },

        async fetchAvailability() {
            this.estimatedPrice = Number(this.estimatedPrice || 0);
            const normalizedStartTime = normalizeTime(this.startTime);

            if (!this.reservationDate || !normalizedStartTime || !this.durationHours || !this.guestCount) {
                this.availability = {
                    ...(this.availability || {}),
                    message: 'Lengkapi tanggal, jam mulai, durasi, dan jumlah tamu untuk mengecek ketersediaan.',
                    is_available: false,
                    start_time: normalizedStartTime || null,
                    end_time: this.endTimePreview() || null,
                };

                return;
            }

            this.loading = true;

            try {
                const url = new URL(this.availabilityUrl, window.location.origin);
                url.searchParams.set('date', this.reservationDate);
                url.searchParams.set('start_time', normalizedStartTime);
                url.searchParams.set('duration_hours', this.durationHours);
                url.searchParams.set('guest_count', this.guestCount);

                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error(`Availability request failed with status ${response.status}`);
                }

                const payload = await response.json();
                this.availability = payload;
                this.estimatedPrice = Number(payload.estimated_price || 0);
                this.estimatedPriceLabel = payload.estimated_price_label || this.estimatedPriceLabel;
                this.startTime = normalizeTime(payload.start_time || normalizedStartTime);
            } catch (error) {
                this.availability = {
                    ...(this.availability || {}),
                    message: 'Gagal memuat ketersediaan reservasi. Coba lagi beberapa saat lagi.',
                    is_available: false,
                };
            } finally {
                this.loading = false;
            }
        },

        isAvailable() {
            return this.availability?.is_available === true;
        },

        durationText() {
            return durationLabel(this.durationHours);
        },

        endTimePreview() {
            if (this.availability?.end_time) {
                return normalizeTime(this.availability.end_time);
            }

            return addHoursToTime(this.startTime, this.durationHours);
        },

        reservationTimeLabel() {
            if (!this.startTime) {
                return 'Belum dipilih';
            }

            if (!isCompleteTime(this.startTime)) {
                return this.startTime;
            }

            const endTime = this.endTimePreview();

            return endTime
                ? `${normalizeTime(this.startTime)} - ${endTime}`
                : normalizeTime(this.startTime);
        },

        availabilityToneClass() {
            if (this.isAvailable()) {
                return 'border-emerald-200 bg-emerald-50 text-emerald-700';
            }

            return 'border-amber-200 bg-amber-50 text-amber-700';
        },

        availabilityMessage() {
            if (this.availability?.message) {
                return this.availability.message;
            }

            if (this.isAvailable()) {
                return this.availability?.available_label || 'Jadwal ini tersedia untuk dipesan.';
            }

            return 'Pilih jadwal reservasi untuk mengecek ketersediaan.';
        },

        operationalLabel() {
            return this.availability?.operational_label || 'Rentang operasional akan tampil setelah jadwal dipilih.';
        },

        priceSummary() {
            return this.estimatedPriceLabel || 'Rp. 0';
        },

        isBusy() {
            return this.loading || this.submitting;
        },

        buttonLoadingLabel() {
            return this.submitting ? 'Memproses...' : 'Memeriksa...';
        },

        formattedReservationDate() {
            if (!this.reservationDate) {
                return 'Belum dipilih';
            }

            const date = new Date(`${this.reservationDate}T00:00:00`);

            if (Number.isNaN(date.getTime())) {
                return this.reservationDate;
            }

            return new Intl.DateTimeFormat('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            }).format(date);
        },

        openConfirmationModal() {
            const form = this.$refs.bookingForm;

            if (!form || this.loading || this.submitting || !this.isAvailable()) {
                return;
            }

            if (typeof form.reportValidity === 'function' && !form.reportValidity()) {
                return;
            }

            this.confirmationModalOpen = true;
        },

        closeConfirmationModal() {
            if (this.submitting) {
                return;
            }

            this.confirmationModalOpen = false;
        },

        submitReservationForm() {
            const form = this.$refs.bookingForm;

            if (!form || this.loading || this.submitting) {
                return;
            }

            if (typeof form.reportValidity === 'function' && !form.reportValidity()) {
                return;
            }

            this.submitting = true;
            this.confirmationModalOpen = false;
            form.submit();
        },
    }));
});
