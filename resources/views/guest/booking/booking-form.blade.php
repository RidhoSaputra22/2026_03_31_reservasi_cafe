@php
    $user = auth()->user();
    $selectedPaymentMethod = old('payment_method', \App\Enums\PaymentMethod::Cash->value);
@endphp

<div x-data="bookingReservationForm({
    availabilityUrl: @js(route('booking.availability', ['slug' => $package['slug']])),
    initialDate: @js($selectedDate),
    initialTime: @js($selectedTime),
    initialGuestCount: @js($guestCount),
    initialSlots: @js($availability['slots']),
    initialMessage: @js($availability['message']),
})">
    <div class="space-y-5 rounded-md border border-gray-100 bg-white p-6 shadow-sm">
        <div class="rounded-md border border-gray-200 bg-gray-50 p-4 text-sm font-light text-gray-600">
            Reservasi akan menggunakan slot aktif dari sistem admin dan meja akan dipilih otomatis sesuai jumlah tamu.
        </div>

        <div class="space-y-2">
            <h1 class="text-4xl font-bold">Reservasi Sekarang</h1>
            <p class="text-sm font-light">Isi data singkat di bawah untuk mengamankan slot kunjunganmu di Cafe Amiko.</p>
        </div>

        @guest
            <div class="space-y-4 rounded-md border border-dashed border-primary/30 bg-primary/5 p-5 text-primary">
                <p class="text-sm font-medium">
                    Kamu perlu masuk sebagai pelanggan terlebih dahulu untuk membuat reservasi.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('login', ['redirect' => url()->current()]) }}"
                        class="rounded-md bg-primary px-4 py-3 text-sm font-semibold text-white transition hover:bg-primary/90">
                        Masuk Sekarang
                    </a>
                    <a href="{{ route('register', ['redirect' => url()->current()]) }}"
                        class="rounded-md border border-primary/20 px-4 py-3 text-sm font-semibold text-primary transition hover:border-primary hover:bg-white">
                        Buat Akun Pelanggan
                    </a>
                </div>
            </div>
        @else
            <form method="POST" action="{{ route('booking.store', ['slug' => $package['slug']]) }}" class="space-y-5">
                @csrf

                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-primary">Nama Lengkap</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name', $user?->name) }}"
                            class="w-full rounded-md border border-gray-200 px-4 py-3 text-gray-700" required>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-primary">Email</label>
                        <input type="email" value="{{ $user?->email }}"
                            class="w-full rounded-md border border-gray-200 bg-gray-100 px-4 py-3 text-gray-700" readonly>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-primary">Nomor Telepon</label>
                        <input type="text" name="customer_phone" value="{{ old('customer_phone', $user?->phone_number) }}"
                            class="w-full rounded-md border border-gray-200 px-4 py-3 text-gray-700" required>
                    </div>
                </div>

                @include('guest.booking.components.booking-callendar')

                <div class="space-y-2">
                    <label class="text-sm font-medium text-primary">Metode Pembayaran</label>
                    <select name="payment_method" class="w-full rounded-md border border-gray-200 px-4 py-3">
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method->value }}" @selected($selectedPaymentMethod === $method->value)>
                                {{ $method->label() }}
                            </option>
                        @endforeach
                    </select>
                    @if ($downPaymentAmount > 0)
                        <p class="text-xs font-light text-gray-500">
                            Sistem akan membuat tagihan DP awal sebesar
                            <span class="font-semibold text-primary">Rp{{ number_format($downPaymentAmount, 0, ',', '.') }}</span>.
                        </p>
                    @endif
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-primary">Catatan Tambahan</label>
                    <textarea name="notes" rows="4" class="w-full rounded-md border border-gray-200 px-4 py-3"
                        placeholder="Misalnya butuh meja dekat jendela, area yang lebih tenang, atau request khusus lainnya.">{{ old('notes') }}</textarea>
                </div>

                <div>
                    <button type="submit" :disabled="loading || !selectedTime"
                        class="w-full rounded-md bg-primary px-4 py-3 font-semibold text-white transition hover:bg-primary/90 disabled:cursor-not-allowed disabled:bg-primary/50">
                        <span x-show="!loading">Kirim Permintaan Reservasi</span>
                        <span x-show="loading" x-cloak>Memuat slot...</span>
                    </button>
                </div>
            </form>
        @endguest
    </div>
</div>
