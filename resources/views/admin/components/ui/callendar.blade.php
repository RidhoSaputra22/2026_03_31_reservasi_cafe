
@props([
    'events' => [],
    'mode' => 'default',
    'selectedDate' => null,
    'showScheduleLegend' => true,
    'showDeadlineLegend' => true,
    'showCustomLegend' => true,
    'customLegendLabel' => 'Event Custom',
    'allowEventCrud' => true,
    'interactive' => true,
    'syncEventName' => null,
    'eventClickName' => null,
    'dateClickName' => null,
    'selectionSyncEvent' => null,
])

@php
    $isSlim = $mode === 'slim';
@endphp

<div x-data="kalenderApp(@js([
    'mode' => $mode,
    'selectedDate' => $selectedDate,
    'customLegendLabel' => $customLegendLabel,
    'allowEventCrud' => $allowEventCrud,
    'interactive' => $interactive,
    'syncEventName' => $syncEventName,
    'eventClickName' => $eventClickName,
    'dateClickName' => $dateClickName,
    'selectionSyncEvent' => $selectionSyncEvent,
]))" x-init="init()" {{ $attributes->merge(['class' => '']) }}>
    @include('components.ui.partials.callendar.navigation')
    @include('components.ui.partials.callendar.grid')
    @include('components.ui.partials.callendar.modal-event')
    @include('components.ui.partials.callendar.modal-event-form')
</div>

@push('scripts')
    <script>
        function kalenderApp(config = {}) {
            return {
                currentDate: new Date(),
                events: @json($events),
                calendarCells: [],
                monthYear: '',
                modalEvents: [],
                modalDate: '',
                modalDateObj: null,
                selectedDate: config.selectedDate || null,
                customEventLabel: config.customLegendLabel || 'Event Custom',
                allowEventCrud: Boolean(config.allowEventCrud ?? true),
                interactive: Boolean(config.interactive ?? true),
                syncEventName: config.syncEventName || null,
                eventClickName: config.eventClickName || null,
                dateClickName: config.dateClickName || null,
                selectionSyncEvent: config.selectionSyncEvent || null,

                colorOptions: [{
                        value: 'primary',
                        label: 'Primary'
                    },
                    {
                        value: 'secondary',
                        label: 'Secondary'
                    },
                    {
                        value: 'accent',
                        label: 'Accent'
                    },
                    {
                        value: 'info',
                        label: 'Info'
                    },
                    {
                        value: 'success',
                        label: 'Success'
                    },
                    {
                        value: 'warning',
                        label: 'Warning'
                    },
                    {
                        value: 'error',
                        label: 'Error'
                    },
                    {
                        value: 'neutral',
                        label: 'Neutral'
                    },
                ],

                eventForm: {
                    id: null,
                    title: '',
                    start: '',
                    end: '',
                    location: '',
                    description: '',
                    color: 'primary'
                },
                eventFormErrors: {},
                eventFormLoading: false,
                dragRangeLabel: '',

                isDragging: false,
                dragStartIndex: null,
                dragEndIndex: null,

                init() {
                    this.selectedDate = this.normalizeDateInput(this.selectedDate);
                    this.normalizeInitialEvents();
                    this.render();
                    this.registerSyncListener();
                    this.registerSelectionListener();

                    document.addEventListener('mouseup', () => {
                        if (this.isDragging) this.onDragEnd();
                    });
                },

                registerSyncListener() {
                    if (!this.syncEventName) {
                        return;
                    }

                    window.addEventListener(this.syncEventName, (event) => {
                        this.replaceExternalEvents(event.detail?.events || []);
                    });
                },

                replaceExternalEvents(events) {
                    this.events = Array.isArray(events) ? events : [];
                    this.normalizeInitialEvents();
                    this.render();
                },

                registerSelectionListener() {
                    if (!this.selectionSyncEvent) {
                        return;
                    }

                    window.addEventListener(this.selectionSyncEvent, (event) => {
                        this.updateSelectedDate(event.detail?.date || null, {
                            syncMonth: event.detail?.syncMonth !== false,
                        });
                    });
                },

                normalizeDateInput(date) {
                    if (!date) {
                        return null;
                    }

                    const normalized = String(date).trim().substring(0, 10);

                    if (/^\d{4}-\d{2}-\d{2}$/.test(normalized)) {
                        return normalized;
                    }

                    const parsed = new Date(date);

                    if (Number.isNaN(parsed.getTime())) {
                        return null;
                    }

                    const offset = parsed.getTimezoneOffset();

                    return new Date(parsed.getTime() - (offset * 60 * 1000)).toISOString().slice(0, 10);
                },

                updateSelectedDate(date, options = {}) {
                    const normalizedDate = this.normalizeDateInput(date);
                    this.selectedDate = normalizedDate;

                    if (normalizedDate && options.syncMonth !== false) {
                        const [year, month] = normalizedDate.split('-').map(Number);

                        if (this.currentDate.getFullYear() !== year || this.currentDate.getMonth() !== month - 1) {
                            this.currentDate = new Date(year, month - 1, 1);
                        }
                    }

                    this.render();
                },

                isSelectedCell(cell) {
                    return Boolean(this.selectedDate && cell?.dateStr === this.selectedDate);
                },

                canDispatchEventClick(event) {
                    return Boolean(this.eventClickName && event);
                },

                canDispatchDateClick(cell) {
                    return Boolean(this.dateClickName && cell?.currentMonth);
                },

                dispatchExternalEventClick(event) {
                    if (!this.canDispatchEventClick(event)) {
                        return;
                    }

                    this.updateSelectedDate(event.start, {
                        syncMonth: false,
                    });

                    window.dispatchEvent(new CustomEvent(this.eventClickName, {
                        detail: {
                            event,
                        },
                    }));
                },

                dispatchExternalDateClick(cell) {
                    if (!this.canDispatchDateClick(cell)) {
                        return;
                    }

                    this.updateSelectedDate(cell.dateStr, {
                        syncMonth: false,
                    });

                    window.dispatchEvent(new CustomEvent(this.dateClickName, {
                        detail: {
                            date: cell.dateStr,
                            cell,
                        },
                    }));
                },

                handlePassiveCellClick(cell) {
                    if (this.interactive || !this.canDispatchDateClick(cell)) {
                        return;
                    }

                    this.dispatchExternalDateClick(cell);
                },

                normalizeInitialEvents() {
                    this.events = (this.events || []).map(e => {
                        const normalized = {
                            ...e
                        };

                        if (normalized.extendedProps?.type === 'custom') {
                            const token = this.getEventColorToken(
                                normalized.color || normalized.extendedProps?.color || 'primary'
                            );

                            normalized.color = token;
                            normalized.extendedProps = {
                                ...(normalized.extendedProps || {}),
                                color: token,
                            };
                        }

                        return normalized;
                    });
                },

                get jadwalEvents() {
                    return this.events.filter(e => Array.isArray(e.daysOfWeek));
                },

                get deadlineEvents() {
                    return this.events.filter(e => e.start && !e.daysOfWeek && e.extendedProps?.type === 'deadline');
                },

                get customEvents() {
                    return this.events.filter(e => e.start && !e.daysOfWeek && e.extendedProps?.type === 'custom');
                },

                get weekSchedule() {
                    const dayOrder = @js(\App\Enums\DayOfWeek::list());
                    return this.jadwalEvents
                        .map(e => ({
                            title: e.title,
                            hari: e.extendedProps?.hari,
                            jam_mulai: e.startTime,
                            jam_selesai: e.endTime,
                            ruangan: e.extendedProps?.ruangan,
                        }))
                        .sort((a, b) => dayOrder.indexOf(a.hari) - dayOrder.indexOf(b.hari));
                },

                get upcomingDeadlines() {
                    const now = new Date();
                    now.setHours(0, 0, 0, 0);
                    return this.deadlineEvents
                        .filter(e => e.start && new Date(e.start) >= now)
                        .sort((a, b) => new Date(a.start) - new Date(b.start))
                        .slice(0, 5)
                        .map(e => ({
                            title: e.title,
                            date: e.start,
                            mata_kuliah: e.extendedProps?.mata_kuliah,
                            status: e.extendedProps?.status,
                            progress: e.extendedProps?.progress,
                        }));
                },

                get upcomingEvents() {
                    const now = new Date();
                    return this.customEvents
                        .filter(e => e.start && new Date(e.start) >= now)
                        .sort((a, b) => new Date(a.start) - new Date(b.start))
                        .slice(0, 5);
                },

                getEventColorToken(color) {
                    const allowed = ['primary', 'secondary', 'accent', 'info', 'success', 'warning', 'error', 'neutral'];
                    return allowed.includes(color) ? color : 'primary';
                },

                getColorPreviewClass(color) {
                    const token = this.getEventColorToken(color);
                    return {
                        primary: 'badge-primary',
                        secondary: 'badge-secondary',
                        accent: 'badge-accent',
                        info: 'badge-info',
                        success: 'badge-success',
                        warning: 'badge-warning',
                        error: 'badge-error',
                        neutral: 'badge-neutral',
                    } [token];
                },

                getEventPillClass(color) {
                    const token = this.getEventColorToken(color);
                    return {
                        primary: 'bg-primary text-primary-content',
                        secondary: 'bg-secondary text-secondary-content',
                        accent: 'bg-accent text-accent-content',
                        info: 'bg-info text-info-content',
                        success: 'bg-success text-success-content',
                        warning: 'bg-warning text-warning-content',
                        error: 'bg-error text-error-content',
                        neutral: 'bg-neutral text-neutral-content',
                    } [token];
                },

                getEventSoftClass(color) {
                    const token = this.getEventColorToken(color);
                    return {
                        primary: 'bg-primary/15 text-primary border-primary/30',
                        secondary: 'bg-secondary/15 text-secondary border-secondary/30',
                        accent: 'bg-accent/15 text-accent border-accent/30',
                        info: 'bg-info/15 text-info border-info/30',
                        success: 'bg-success/15 text-success border-success/30',
                        warning: 'bg-warning/15 text-warning border-warning/30',
                        error: 'bg-error/15 text-error border-error/30',
                        neutral: 'bg-neutral/15 text-neutral border-neutral/30',
                    } [token];
                },

                getEventDotClass(color) {
                    const token = this.getEventColorToken(color);
                    return {
                        primary: 'bg-primary',
                        secondary: 'bg-secondary',
                        accent: 'bg-accent',
                        info: 'bg-info',
                        success: 'bg-success',
                        warning: 'bg-warning',
                        error: 'bg-error',
                        neutral: 'bg-neutral',
                    } [token];
                },

                getEventTextClass(color) {
                    const token = this.getEventColorToken(color);
                    return {
                        primary: 'text-primary',
                        secondary: 'text-secondary',
                        accent: 'text-accent',
                        info: 'text-info',
                        success: 'text-success',
                        warning: 'text-warning',
                        error: 'text-error',
                        neutral: 'text-neutral',
                    } [token];
                },

                getEventMutedTextClass(color) {
                    const token = this.getEventColorToken(color);
                    return {
                        primary: 'text-primary/70',
                        secondary: 'text-secondary/70',
                        accent: 'text-accent/70',
                        info: 'text-info/70',
                        success: 'text-success/70',
                        warning: 'text-warning/70',
                        error: 'text-error/70',
                        neutral: 'text-neutral/70',
                    } [token];
                },

                render() {
                    const year = this.currentDate.getFullYear();
                    const month = this.currentDate.getMonth();
                    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                    ];
                    this.monthYear = months[month] + ' ' + year;

                    const firstDay = new Date(year, month, 1);
                    let startDay = firstDay.getDay() - 1;
                    if (startDay < 0) startDay = 6;

                    const daysInMonth = new Date(year, month + 1, 0).getDate();
                    const daysInPrevMonth = new Date(year, month, 0).getDate();
                    const today = new Date();

                    const cells = [];

                    for (let i = startDay - 1; i >= 0; i--) {
                        cells.push({
                            day: daysInPrevMonth - i,
                            currentMonth: false,
                            isToday: false,
                            dateStr: null,
                            events: []
                        });
                    }

                    for (let d = 1; d <= daysInMonth; d++) {
                        const date = new Date(year, month, d);
                        const dayOfWeek = date.getDay();
                        const isToday = date.toDateString() === today.toDateString();
                        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;

                        const events = [];

                        this.jadwalEvents.forEach(e => {
                            if (e.daysOfWeek.includes(dayOfWeek)) {
                                events.push({
                                    title: e.title,
                                    type: 'jadwal',
                                    jam_mulai: e.startTime || null,
                                    jam_selesai: e.endTime || null,
                                    ruangan: e.extendedProps?.ruangan || null,
                                    hari: e.extendedProps?.hari || null,
                                });
                            }
                        });

                        this.deadlineEvents.forEach(e => {
                            if (e.start && String(e.start).substring(0, 10) === dateStr) {
                                events.push({
                                    title: e.title,
                                    type: 'deadline',
                                    mata_kuliah: e.extendedProps?.mata_kuliah || null,
                                    status: e.extendedProps?.status || null,
                                    progress: e.extendedProps?.progress ?? null,
                                    date: e.start || null,
                                });
                            }
                        });

                        this.customEvents.forEach(e => {
                            if (e.start && String(e.start).substring(0, 10) === dateStr) {
                                events.push({
                                    title: e.title,
                                    type: 'custom',
                                    id: e.id || null,
                                    eventId: e.extendedProps?.eventId || null,
                                    attendanceId: e.extendedProps?.attendanceId || null,
                                    start: e.start || null,
                                    end: e.end || null,
                                    location: e.extendedProps?.location || null,
                                    description: e.extendedProps?.description || null,
                                    color: this.getEventColorToken(e.color || e.extendedProps?.color ||
                                        'primary'),
                                });
                            }
                        });

                        cells.push({
                            day: d,
                            currentMonth: true,
                            isToday,
                            dateStr,
                            events
                        });
                    }

                    const remaining = 42 - cells.length;
                    for (let i = 1; i <= remaining; i++) {
                        cells.push({
                            day: i,
                            currentMonth: false,
                            isToday: false,
                            dateStr: null,
                            events: []
                        });
                    }

                    this.customEvents.forEach(e => {
                        if (!e.start || !e.end) return;

                        const sDay = new Date(new Date(e.start).toDateString());
                        const enDay = new Date(new Date(e.end).toDateString());
                        if (sDay >= enDay) return;

                        const monthStartDate = new Date(year, month, 1);
                        const monthEndDate = new Date(year, month, daysInMonth);
                        if (enDay < monthStartDate || sDay > monthEndDate) return;

                        const clampedStart = sDay < monthStartDate ? monthStartDate : sDay;
                        const clampedEnd = enDay > monthEndDate ? monthEndDate : enDay;
                        const sd = clampedStart.getDate();
                        const ed = clampedEnd.getDate();
                        const isLeftClamped = sDay < monthStartDate;
                        const isRightClamped = enDay > monthEndDate;

                        for (let d = sd; d <= ed; d++) {
                            const ci = startDay + d - 1;
                            if (!cells[ci] || !cells[ci].currentMonth) continue;

                            if (!cells[ci].spanBars) cells[ci].spanBars = [];
                            const dow = new Date(year, month, d).getDay();
                            // console.log(e);


                            cells[ci].spanBars.push({
                                title: e.title,
                                location: e.extendedProps.location,
                                start: e.start,
                                end: e.end,
                                eventId: e.extendedProps?.eventId,
                                color: this.getEventColorToken(e.color || e.extendedProps?.color ||
                                    'primary'),
                                isStart: d === sd && !isLeftClamped,
                                isEnd: d === ed && !isRightClamped,
                                showLabel: (d === sd && !isLeftClamped) || dow === 1 || isLeftClamped,
                            });

                            cells[ci].events = cells[ci].events.filter(
                                ev => !(ev.type === 'custom' && ev.eventId === e.extendedProps?.eventId)
                            );
                        }
                    });

                    this.calendarCells = cells;
                    // console.log(cells);


                },

                isInDragSelection(index) {
                    if (!this.isDragging || this.dragStartIndex === null || this.dragEndIndex === null) return false;
                    const min = Math.min(this.dragStartIndex, this.dragEndIndex);
                    const max = Math.max(this.dragStartIndex, this.dragEndIndex);
                    return index >= min && index <= max;
                },

                onCellMouseDown(index, cell) {
                    if (!this.interactive) return;
                    if (!cell.currentMonth) return;
                    this.isDragging = true;
                    this.dragStartIndex = index;
                    this.dragEndIndex = index;
                },

                onCellMouseEnter(index, cell) {
                    if (!this.interactive) return;
                    if (!this.isDragging) return;
                    if (cell.currentMonth) this.dragEndIndex = index;
                },

                onDragEnd() {
                    if (!this.interactive) return;
                    if (!this.isDragging) return;
                    this.isDragging = false;

                    const startIdx = Math.min(this.dragStartIndex, this.dragEndIndex);
                    const endIdx = Math.max(this.dragStartIndex, this.dragEndIndex);
                    const startCell = this.calendarCells[startIdx];
                    const endCell = this.calendarCells[endIdx];

                    this.dragStartIndex = null;
                    this.dragEndIndex = null;

                    if (!startCell || !startCell.currentMonth) return;

                    if (startIdx === endIdx) {
                        this.showEventModal(startCell);
                        return;
                    }

                    if (endCell?.currentMonth) {
                        const y = this.currentDate.getFullYear();
                        const m = String(this.currentDate.getMonth() + 1).padStart(2, '0');
                        const sd = String(startCell.day).padStart(2, '0');
                        const ed = String(endCell.day).padStart(2, '0');
                        this.modalDateObj = startCell;

                        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                        ];
                        const mName = months[this.currentDate.getMonth()];
                        this.dragRangeLabel = `${startCell.day}–${endCell.day} ${mName} ${y}`;

                        this.openCreateEvent(`${y}-${m}-${sd}T08:00`, `${y}-${m}-${ed}T17:00`);
                    }
                },

                showEventModal(cell) {
                    const spanEvents = (cell.spanBars || []).map(b => {
                        const full = this.customEvents.find(e => e.extendedProps?.eventId === b.eventId);
                        return {
                            type: 'custom',
                            title: b.title,
                            eventId: b.eventId,
                            start: full?.start || null,
                            end: full?.end || null,
                            location: full?.extendedProps?.location || null,
                            description: full?.extendedProps?.description || null,
                            color: this.getEventColorToken(full?.color || full?.extendedProps?.color || b.color ||
                                'primary'),
                        };
                    });

                    this.modalEvents = [
                        ...cell.events.map(e => ({
                            ...e,
                            color: e.type === 'custom' ? this.getEventColorToken(e.color || 'primary') : e.color
                        })),
                        ...spanEvents
                    ];

                    this.modalDateObj = cell;
                    this.dragRangeLabel = '';

                    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                    ];
                    this.modalDate = cell.day + ' ' + months[this.currentDate.getMonth()] + ' ' + this.currentDate
                        .getFullYear();

                    setTimeout(() => {
                        document.getElementById('modal-event').showModal();
                    }, 10);
                },

                _dateTimeForModal(hhmm = '08:00') {
                    const y = this.currentDate.getFullYear();
                    const m = String(this.currentDate.getMonth() + 1).padStart(2, '0');
                    const d = this.modalDateObj ? String(this.modalDateObj.day).padStart(2, '0') : '01';
                    return `${y}-${m}-${d}T${hhmm}`;
                },

                openCreateEvent(startStr = null, endStr = null) {
                    if (!this.allowEventCrud) return;
                    this.eventForm = {
                        id: null,
                        title: '',
                        start: startStr || this._dateTimeForModal('08:00'),
                        end: endStr || this._dateTimeForModal('09:00'),
                        location: '',
                        description: '',
                        color: 'primary',
                    };
                    this.eventFormErrors = {};
                    document.getElementById('modal-event-form').showModal();
                },

                openEditEvent(event) {
                    if (!this.allowEventCrud) return;
                    this.eventForm = {
                        id: event.eventId,
                        title: event.title,
                        start: event.start ? event.start.replace(' ', 'T').substring(0, 16) : '',
                        end: event.end ? event.end.replace(' ', 'T').substring(0, 16) : '',
                        location: event.location || '',
                        description: event.description || '',
                        color: this.getEventColorToken(event.color || event.extendedProps?.color || 'primary'),
                    };
                    this.eventFormErrors = {};
                    document.getElementById('modal-event-form').showModal();
                },

                closeEventForm() {
                    document.getElementById('modal-event-form').close();
                },

                async submitEventForm() {
                    if (!this.allowEventCrud) return;
                    this.eventFormErrors = {};

                    if (!this.eventForm.title.trim()) {
                        this.eventFormErrors.title = 'Judul wajib diisi';
                        return;
                    }

                    if (!this.eventForm.start) {
                        this.eventFormErrors.start = 'Waktu mulai wajib diisi';
                        return;
                    }

                    this.eventFormLoading = true;
                    const isEdit = !!this.eventForm.id;
                    const url = isEdit ? `/events/${this.eventForm.id}` : '/events';
                    const method = isEdit ? 'PUT' : 'POST';

                    try {
                        const res = await fetch(url, {
                            method,
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                title: this.eventForm.title,
                                start: this.eventForm.start,
                                end: this.eventForm.end || null,
                                location: this.eventForm.location || null,
                                description: this.eventForm.description || null,
                                color: this.getEventColorToken(this.eventForm.color || 'primary'),
                            }),
                        });

                        if (!res.ok) {
                            const data = await res.json();
                            if (data.errors) this.eventFormErrors = data.errors;
                            return;
                        }

                        const saved = await res.json();
                        const savedColor = this.getEventColorToken(saved.color || 'primary');

                        const mapped = {
                            title: saved.title,
                            type: 'custom',
                            eventId: saved.id,
                            start: saved.start ? saved.start.replace('T', ' ') : saved.start,
                            end: saved.end ? saved.end.replace('T', ' ') : saved.end,
                            location: saved.location,
                            description: saved.description,
                            color: savedColor,
                        };

                        if (isEdit) {
                            const idx = this.modalEvents.findIndex(e => e.type === 'custom' && e.eventId === saved.id);
                            if (idx !== -1) this.modalEvents.splice(idx, 1, mapped);

                            const gi = this.events.findIndex(e => e.id === 'event-' + saved.id);
                            if (gi !== -1) {
                                this.events[gi].title = saved.title;
                                this.events[gi].extendedProps.description = saved.description;
                                this.events[gi].extendedProps.location = saved.location;
                                this.events[gi].extendedProps.color = savedColor;
                                this.events[gi].color = savedColor;
                                this.events[gi].start = saved.start;
                                this.events[gi].end = saved.end;
                            }

                            this.render();
                        } else {
                            this.events.push({
                                id: 'event-' + saved.id,
                                title: saved.title,
                                start: saved.start,
                                end: saved.end,
                                color: savedColor,
                                extendedProps: {
                                    type: 'custom',
                                    description: saved.description,
                                    location: saved.location,
                                    eventId: saved.id,
                                    color: savedColor,
                                },
                            });

                            this.render();
                            this.modalEvents.push(mapped);
                        }

                        this.closeEventForm();
                    } finally {
                        this.eventFormLoading = false;
                    }
                },

                async deleteEvent(event, idx) {
                    if (!this.allowEventCrud) return;
                    if (!confirm(`Hapus event "${event.title}"?`)) return;

                    try {
                        const res = await fetch(`/events/${event.eventId}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                        });

                        if (res.ok || res.status === 204) {
                            this.modalEvents.splice(idx, 1);

                            const gi = this.events.findIndex(e => e.id === 'event-' + event.eventId);
                            if (gi !== -1) this.events.splice(gi, 1);

                            this.render();
                        }
                    } catch (e) {
                        console.error('Gagal menghapus event', e);
                    }
                },

                prevMonth() {
                    this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() - 1, 1);
                    this.render();
                },

                nextMonth() {
                    this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 1);
                    this.render();
                },

                formatDate(dateStr) {
                    const d = new Date(dateStr);
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
                    return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
                },
            };
        }
    </script>
@endpush
