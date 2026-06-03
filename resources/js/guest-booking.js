import Alpine from 'alpinejs';

const BOOKING_WEEKDAYS = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

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

function parseDate(value) {
    const normalized = normalizeDate(value);

    if (!normalized) {
        return null;
    }

    const [year, month, day] = normalized.split('-').map(Number);

    return new Date(year, month - 1, day);
}

function formatDateLabel(value, options = {}) {
    const date = parseDate(value);

    if (!date) {
        return '';
    }

    return new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        ...options,
    }).format(date);
}

function cloneSlots(slots = []) {
    return Array.isArray(slots) ? slots.map((slot) => ({ ...slot })) : [];
}

document.addEventListener('alpine:init', () => {
    Alpine.data('bookingReservationForm', (config = {}) => ({
        availabilityUrl: config.availabilityUrl || '',
        today: normalizeDate(config.today) || normalizeDate(new Date()),
        weekdayLabels: BOOKING_WEEKDAYS,
        guestCount: Number(config.initialGuestCount || 2),
        maxGuestCount: Number(config.maxGuestCount || 12),
        selectedDate: normalizeDate(config.initialDate),
        selectedTime: config.initialTime || '',
        selectedDateLabel: '',
        committedSlots: cloneSlots(config.initialSlots),
        committedMessage: config.initialMessage || null,
        pickerOpen: false,
        pickerStep: 'calendar',
        pickerDate: '',
        pickerTime: '',
        pickerSlots: [],
        pickerMessage: null,
        loading: false,
        visibleMonth: 0,
        visibleYear: 0,
        guestCountDebounceId: null,

        init() {
            this.syncDateLabel();
            this.syncCalendarReference(this.selectedDate || this.today);

            if (!this.selectedTime) {
                const firstAvailable = this.committedSlots.find((slot) => slot.available);
                this.selectedTime = firstAvailable ? firstAvailable.time : '';
            }

            this.$watch('guestCount', (value) => {
                const normalizedGuestCount = Math.max(1, Math.min(this.maxGuestCount, Number(value || 1)));
                this.guestCount = normalizedGuestCount;

                window.clearTimeout(this.guestCountDebounceId);
                this.guestCountDebounceId = window.setTimeout(() => {
                    const targetDate = this.pickerOpen ? (this.pickerDate || this.selectedDate || this.today) : (this.selectedDate || this.today);

                    if (!targetDate) {
                        return;
                    }

                    if (this.pickerOpen) {
                        this.loadPickerAvailability(targetDate);
                    } else {
                        this.loadCommittedAvailability(targetDate);
                    }
                }, 350);
            });
        },

        openPicker() {
            this.pickerOpen = true;
            this.pickerStep = 'calendar';
            this.pickerDate = this.selectedDate || this.today;
            this.pickerTime = this.selectedTime || '';
            this.pickerSlots = cloneSlots(this.committedSlots);
            this.pickerMessage = this.committedMessage;
            this.syncCalendarReference(this.pickerDate || this.today);
            document.body.classList.add('overflow-hidden');
        },

        closePicker() {
            this.pickerOpen = false;
            this.pickerStep = 'calendar';
            this.pickerDate = this.selectedDate || this.today;
            this.pickerTime = this.selectedTime || '';
            this.pickerSlots = [];
            this.pickerMessage = null;
            document.body.classList.remove('overflow-hidden');
        },

        syncDateLabel() {
            this.selectedDateLabel = formatDateLabel(this.selectedDate);
        },

        syncCalendarReference(referenceDate) {
            const date = parseDate(referenceDate) || parseDate(this.today) || new Date();
            this.visibleMonth = date.getMonth();
            this.visibleYear = date.getFullYear();
        },

        calendarTitle() {
            return new Intl.DateTimeFormat('id-ID', {
                month: 'long',
                year: 'numeric',
            }).format(new Date(this.visibleYear, this.visibleMonth, 1));
        },

        calendarDays() {
            const firstDayOfMonth = new Date(this.visibleYear, this.visibleMonth, 1);
            const startOffset = (firstDayOfMonth.getDay() + 6) % 7;
            const gridStart = new Date(this.visibleYear, this.visibleMonth, 1 - startOffset);

            return Array.from({ length: 42 }, (_, index) => {
                const date = new Date(gridStart);
                date.setDate(gridStart.getDate() + index);

                const normalizedDate = normalizeDate(date);

                return {
                    key: `${normalizedDate}-${index}`,
                    date: normalizedDate,
                    day: date.getDate(),
                    month: date.getMonth(),
                    year: date.getFullYear(),
                    currentMonth: date.getMonth() === this.visibleMonth,
                    isToday: normalizedDate === this.today,
                    disabled: normalizedDate < this.today,
                };
            });
        },

        calendarDayClass(day) {
            return {
                'booking-calendar__day--outside': !day.currentMonth,
                'booking-calendar__day--disabled': day.disabled,
                'booking-calendar__day--today': day.isToday,
                'booking-calendar__day--selected': day.date === (this.pickerDate || this.selectedDate),
            };
        },

        goToPreviousMonth() {
            const previousMonth = new Date(this.visibleYear, this.visibleMonth - 1, 1);
            this.visibleMonth = previousMonth.getMonth();
            this.visibleYear = previousMonth.getFullYear();
        },

        goToNextMonth() {
            const nextMonth = new Date(this.visibleYear, this.visibleMonth + 1, 1);
            this.visibleMonth = nextMonth.getMonth();
            this.visibleYear = nextMonth.getFullYear();
        },

        jumpToToday() {
            this.syncCalendarReference(this.today);
        },

        async selectCalendarDay(day) {
            if (day.disabled) {
                return;
            }

            const loaded = await this.loadPickerAvailability(day.date);

            if (loaded) {
                this.pickerStep = 'slots';
            }
        },

        async loadCommittedAvailability(date) {
            const payload = await this.fetchAvailability(date, this.selectedTime);

            if (!payload) {
                return false;
            }

            this.selectedDate = payload.date;
            this.selectedTime = payload.time;
            this.committedSlots = payload.slots;
            this.committedMessage = payload.message;
            this.syncDateLabel();

            return true;
        },

        async loadPickerAvailability(date) {
            const payload = await this.fetchAvailability(date, this.pickerTime || this.selectedTime);

            if (!payload) {
                return false;
            }

            this.pickerDate = payload.date;
            this.pickerTime = payload.time;
            this.pickerSlots = payload.slots;
            this.pickerMessage = payload.message;
            this.syncCalendarReference(this.pickerDate);

            return true;
        },

        async fetchAvailability(date, currentTime = '') {
            if (!date || !this.guestCount) {
                return null;
            }

            this.loading = true;

            try {
                const url = new URL(this.availabilityUrl, window.location.origin);
                url.searchParams.set('date', date);
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

                const data = await response.json();
                const slots = Array.isArray(data.slots) ? data.slots : [];
                const currentAvailable = slots.find((slot) => slot.time === currentTime && slot.available);
                const firstAvailable = slots.find((slot) => slot.available);

                return {
                    date: normalizeDate(data.date || date),
                    slots,
                    message: data.message || null,
                    time: currentAvailable ? currentAvailable.time : (firstAvailable ? firstAvailable.time : ''),
                };
            } catch (error) {
                const fallbackMessage = 'Gagal memuat ketersediaan slot. Coba lagi beberapa saat lagi.';

                if (this.pickerOpen) {
                    this.pickerMessage = fallbackMessage;
                } else {
                    this.committedMessage = fallbackMessage;
                }

                return null;
            } finally {
                this.loading = false;
            }
        },

        selectPickerTime(time) {
            const selectedSlot = this.pickerSlots.find((slot) => slot.time === time && slot.available);

            if (!selectedSlot) {
                return;
            }

            this.pickerTime = selectedSlot.time;
        },

        timeCardClass(slot) {
            if (!slot.available) {
                return 'booking-time-card--disabled';
            }

            return slot.time === this.pickerTime ? 'booking-time-card--active' : 'booking-time-card--idle';
        },

        applyPickerSelection() {
            if (!this.pickerDate || !this.pickerTime) {
                return;
            }

            this.selectedDate = this.pickerDate;
            this.selectedTime = this.pickerTime;
            this.committedSlots = cloneSlots(this.pickerSlots);
            this.committedMessage = this.pickerMessage;
            this.syncDateLabel();
            this.closePicker();
        },

        reservationSelectionLabel() {
            if (!this.selectedDate) {
                return 'Belum ada jadwal yang dipilih';
            }

            const slot = this.selectedSlot();

            if (!slot) {
                return this.selectedDateLabel || formatDateLabel(this.selectedDate);
            }

            return `${this.selectedDateLabel || formatDateLabel(this.selectedDate)} · ${slot.label}`;
        },

        selectedSlot() {
            return this.committedSlots.find((slot) => slot.time === this.selectedTime) || null;
        },

        selectedSlotLabel() {
            const slot = this.selectedSlot();

            return slot ? `${slot.name} · ${slot.label}` : 'Belum dipilih';
        },

        selectionAvailabilityLabel() {
            const slot = this.selectedSlot();

            if (slot?.available_label) {
                return slot.available_label;
            }

            return this.committedMessage || 'Pilih tanggal dan slot terlebih dahulu';
        },

        pickerDateLabel() {
            return formatDateLabel(this.pickerDate || this.selectedDate);
        },
    }));
});
