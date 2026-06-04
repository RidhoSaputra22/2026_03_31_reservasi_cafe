<x-modal open="confirmationModalOpen" onClose="closeConfirmationModal()" title="Cek Data Anda" maxWidth="max-w-lg"
    wrapperClass="relative flex min-h-full items-end justify-center px-4 py-6 sm:items-center"
    panelClass="rounded-md bg-white p-6 shadow-2xl" overlayClass="bg-black/55" closeLabel="Tutup">
    @php
        $pendingPaymentTimeoutMinutes = max(1, (int) config('reservations.pending_payment_timeout_minutes', 60));
    @endphp
    <div class="space-y-2">
        <p class="text-sm text-gray-500">
            Pastikan detail reservasi sudah benar. Setelah klik tombol buat reservasi, sistem akan menyiapkan
            pembayaran lalu menampilkan popup Midtrans secara otomatis.
        </p>
    </div>

    <div class="mt-5 space-y-3 rounded-md bg-gray-50 p-4 text-sm text-gray-600">
        <div class="flex items-start justify-between gap-4">
            <span>Paket</span>
            <span class="text-right font-semibold text-primary">{{ $package['name'] }}</span>
        </div>
        <div class="flex items-start justify-between gap-4">
            <span>Tanggal</span>
            <span class="text-right font-semibold text-primary" x-text="formattedReservationDate()"></span>
        </div>
        <div class="flex items-start justify-between gap-4">
            <span>Jam Reservasi</span>
            <span class="text-right font-semibold text-primary" x-text="reservationTimeLabel()"></span>
        </div>
        <div class="flex items-start justify-between gap-4">
            <span>Durasi</span>
            <span class="text-right font-semibold text-primary" x-text="durationText()"></span>
        </div>
        <div class="flex items-start justify-between gap-4">
            <span>Jumlah Tamu</span>
            <span class="text-right font-semibold text-primary" x-text="`${guestCount} tamu`"></span>
        </div>
        <div class="flex items-start justify-between gap-4 border-t border-gray-200 pt-3">
            <span>Total Biaya</span>
            <span class="text-right text-lg font-bold text-primary" x-text="priceSummary()"></span>
        </div>
        @if ($downPaymentAmount > 0)
            <div class="rounded-md border border-primary/15 bg-primary/5 px-4 py-3 text-xs text-primary">
                Pembayaran awal melalui Midtrans sebesar
                <span class="font-semibold">Rp {{ number_format($downPaymentAmount, 0, ',', '.') }}</span>.
                Jika belum dibayar dalam {{ $pendingPaymentTimeoutMinutes }} menit, reservasi akan dibatalkan otomatis.
            </div>
        @endif
    </div>

    <x-slot:footer>
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button type="button" @click="closeConfirmationModal()" :disabled="submitting"
                class="rounded-md border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-600 transition hover:border-gray-300 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60">
                Kembali
            </button>
            <button type="button" @click="submitReservationForm()" :disabled="submitting || loading"
                :class="{ 'is-loading': isBusy() }"
                class="guest-loading-button rounded-md bg-primary px-4 py-3 text-sm font-semibold text-white transition hover:bg-primary/90 disabled:cursor-not-allowed disabled:bg-primary/50">
                <span class="guest-loading-button__label">Buat Reservasi</span>
                <span class="guest-loading-button__state" x-cloak>
                    <span class="guest-loading-button__spinner" aria-hidden="true"></span>
                    <span x-text="buttonLoadingLabel()"></span>
                </span>
            </button>
        </div>
    </x-slot:footer>
</x-modal>
