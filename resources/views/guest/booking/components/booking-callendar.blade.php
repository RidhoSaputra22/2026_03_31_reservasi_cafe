<div class="space-y-4">


    <div class="grid gap-4 md:grid-cols-2">
        <div class="space-y-2">
            <label class="text-sm font-medium text-primary">Tanggal Reservasi</label>
            <input type="date" name="reservation_date" x-model="reservationDate" min="{{ now()->toDateString() }}" lang="id"
                class="w-full rounded-md border border-gray-200 px-4 py-3 text-gray-700" required>
            @error('reservation_date')
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-primary">Jam Mulai</label>
            <input type="text" name="start_time" x-model="startTime" lang="id"
                inputmode="numeric" maxlength="5" autocomplete="off" spellcheck="false"
                pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$" placeholder="08:00"
                title="Gunakan format 24 jam, misalnya 08:00 atau 17:30."
                oninput="this.value = window.formatTime24hInput(this.value)"
                onblur="this.value = window.normalizeTime24hInput(this.value)"
                class="w-full rounded-md border border-gray-200 px-4 py-3 text-gray-700" required>
            <p class="text-xs font-light text-gray-500">Isi jam mulai sendiri sesuai rentang reservasi aktif.</p>
            @error('start_time')
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div class="space-y-2">
            <label class="text-sm font-medium text-primary">Durasi Reservasi</label>
            <select name="duration_hours" x-model.number="durationHours"
                class="w-full rounded-md border border-gray-200 px-4 py-3 text-gray-700" required>
                @foreach ($durationOptions as $hours)
                    <option value="{{ $hours }}">{{ $hours }} jam</option>
                @endforeach
            </select>
            <p class="text-xs font-light text-gray-500">Setiap perubahan durasi akan memperbarui cek ketersediaan dan estimasi harga.</p>
            @error('duration_hours')
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-primary">Jumlah Tamu</label>
            <input type="number" name="guest_count" min="1" max="{{ $maxGuestCount }}" x-model.number="guestCount"
                class="w-full rounded-md border border-gray-200 px-4 py-3 text-gray-700" required>
            <p class="text-xs font-light text-gray-500">
                Maksimal <span class="font-semibold text-primary">{{ $maxGuestCount }} tamu</span> mengikuti kapasitas meja aktif.
            </p>
            @error('guest_count')
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="rounded-md border p-4 text-sm font-light"
        :class="availabilityToneClass()">
        <p class="font-semibold">Status Jadwal</p>
        <p class="mt-1" x-text="availabilityMessage()"></p>
        <p class="mt-2 text-xs" x-text="operationalLabel()"></p>
    </div>




</div>
