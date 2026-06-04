@php
    $user = auth()->user();
    $selectedPaymentMethod = old('payment_method', \App\Enums\PaymentMethod::Cash->value);
    $downPaymentAmount = $downPaymentAmount ?? 0;
    $customerName = old('customer_name', $user?->name ?? '');
    $customerPhone = old('customer_phone', $user?->phone_number ?? '');
    $customerEmail = $user?->email ?? '';
    $profileSeed = trim($customerName !== '' ? $customerName : ($customerEmail !== '' ? $customerEmail : 'Cafe Amiko'));
    $profileWords = preg_split('/\s+/', $profileSeed, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $profileInitials = '';

    foreach (array_slice($profileWords, 0, 2) as $word) {
        $profileInitials .= strtoupper(substr($word, 0, 1));
    }

    if ($profileInitials === '') {
        $profileInitials = strtoupper(substr($profileSeed, 0, 2));
    }
@endphp

<div>
    <div class="space-y-5 rounded-md border border-gray-100 bg-white p-6 shadow-sm ">
        <div class="space-y-3">
            <div class="rounded-md border border-gray-200 bg-gray-50 p-4 text-sm font-light text-gray-600">
                Tentukan tanggal, jam mulai, dan durasi kunjunganmu. Sistem akan menyesuaikan dengan rentang jam aktif admin dan menghitung total harga secara otomatis.
            </div>

            <div class="space-y-2">
                <h1 class="text-4xl font-bold">Reservasi Sekarang</h1>
                <p class="text-sm font-light">Isi data singkat di bawah untuk mengamankan slot kunjunganmu di Cafe Amiko.</p>
            </div>
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
            <form method="POST" action="{{ route('booking.store', ['slug' => $package['slug']]) }}" class="space-y-4">
                @csrf

                <div class="">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary text-lg font-semibold text-white shadow-sm">
                            {{ substr($profileInitials, 0, 2) }}
                        </div>

                        <div class="flex-1 ">
                            <div class="">
                                <p class="text-lg font-semibold text-primary">
                                    {{ $customerName !== '' ? $customerName : 'Nama belum tersedia' }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ $customerEmail !== '' ? $customerEmail : 'Email belum tersedia' }}

                                </p>

                            </div>


                        </div>
                    </div>

                    <input type="hidden" name="customer_name" value="{{ $customerName }}">
                    <input type="hidden" name="customer_phone" value="{{ $customerPhone }}">

                    @error('customer_name')
                        <p class="mt-3 text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    @error('customer_phone')
                        <p class="mt-3 text-xs text-red-600">{{ $message }}</p>
                    @enderror
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



                <div>
                    <button type="submit" :disabled="loading || !reservationDate || !startTime || !isAvailable()"
                        class="w-full rounded-md bg-primary px-4 py-3 font-semibold text-white transition hover:bg-primary/90 disabled:cursor-not-allowed disabled:bg-primary/50">
                        <span x-show="!loading">Kirim Permintaan Reservasi</span>
                        <span x-show="loading" x-cloak>Memeriksa jadwal...</span>
                    </button>
                </div>
            </form>
        @endguest
    </div>
</div>
