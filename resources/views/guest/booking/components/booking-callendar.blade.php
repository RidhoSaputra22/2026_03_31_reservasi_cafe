<div class="space-y-4">
    <div class="rounded-md border border-primary/10 bg-primary/5 p-4 text-sm text-primary">
        <p class="font-semibold">{{ $package['price'] }}</p>
        <p class="mt-1 font-light">{{ $package['pricing_summary'] ?? 'Harga paket akan dihitung otomatis sesuai durasi yang dipilih.' }}</p>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div class="space-y-2">
            <label class="text-sm font-medium text-primary">Tanggal Reservasi</label>
            <input type="date" name="reservation_date" x-model="reservationDate" min="{{ now()->toDateString() }}"
                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-700" required>
            @error('reservation_date')
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-primary">Jam Mulai</label>
            <input type="time" name="start_time" x-model="startTime"
                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-700" required>
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
                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-700" required>
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
                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-700" required>
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

    <div class="grid gap-3 text-sm font-light text-gray-600 md:grid-cols-2">
        <div class="rounded-md border border-gray-100 bg-gray-50 p-4">
            Estimasi harga: <span class="font-semibold text-primary" x-text="priceSummary()"></span>
        </div>
        <div class="rounded-md border border-gray-100 bg-gray-50 p-4">
            Jam reservasi: <span class="font-semibold text-primary" x-text="reservationTimeLabel()"></span>
        </div>
    </div>

    <div class="grid gap-3 text-sm font-light text-gray-600 md:grid-cols-2">
        <div class="rounded-md border border-gray-100 bg-gray-50 p-4">
            Durasi dipilih: <span class="font-semibold text-primary" x-text="durationText()"></span>
        </div>
        <div class="rounded-md border border-gray-100 bg-gray-50 p-4">
            <span x-show="availability?.recommended_table">Rekomendasi meja: <span class="font-semibold text-primary" x-text="availability.recommended_table"></span></span>
            <span x-show="!availability?.recommended_table">Meja akan dipilih otomatis setelah jadwal valid.</span>
        </div>
    </div>

    <div class="rounded-md border border-dashed border-gray-200 bg-white p-4 text-sm font-light text-gray-600"
        x-show="availability?.active_windows?.length">
        <p class="font-semibold text-primary">Rentang reservasi aktif</p>
        <p class="mt-1" x-text="(availability?.active_windows || []).join(', ')"></p>
    </div>
</div>
