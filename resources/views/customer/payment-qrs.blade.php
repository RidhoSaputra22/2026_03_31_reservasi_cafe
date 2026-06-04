<x-layouts.app>
    <div>
        @include('guest.components.site-navbar')

        <section class="mx-auto min-h-screen w-full max-w-5xl space-y-8 px-6 py-20 md:px-12">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div class="space-y-2">
                    <p class="text-sm uppercase tracking-[0.3em] text-primary/60">QR Pembayaran</p>
                    <h1 class="text-4xl font-semibold text-primary">QR Paket Sudah DP</h1>
                    <p class="max-w-3xl text-sm font-light text-gray-600">
                        QR ini hanya berisi kode pembayaran DP. Admin bisa scan kode ini lalu mencari pembayarannya dari panel admin.
                    </p>
                </div>
                <a href="{{ route('customer.profile') }}"
                    class="inline-flex rounded-md border border-primary/20 px-4 py-3 text-sm font-semibold text-primary transition hover:border-primary hover:bg-primary/5">
                    Kembali ke Profil
                </a>
            </div>

            @if (! $reservation)
                <div class="rounded-md border border-dashed border-gray-200 bg-white p-10 text-center shadow-sm">
                    <h2 class="text-xl font-semibold text-primary">Belum ada QR pembayaran.</h2>
                    <p class="mt-3 text-sm font-light text-gray-500">
                        QR akan muncul setelah reservasi memiliki DP yang sudah berhasil dibayar.
                    </p>
                </div>
            @else
                @php($downPayment = $reservation->latestPaidDownPayment())

                <article class="rounded-md border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-3xl font-semibold text-primary">
                                    {{ $reservation->package_name ?? 'Reservasi Cafe Amiko' }}
                                </h2>
                                <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                                    QR DP
                                </span>
                            </div>

                            <div class="space-y-1 text-sm text-gray-600">
                                <p>{{ $reservation->reservation_code }}</p>
                                <p>
                                    {{ $reservation->reservation_date?->translatedFormat('d F Y') }} •
                                    {{ substr($reservation->start_time, 0, 5) }} - {{ substr((string) $reservation->end_time, 0, 5) }}
                                </p>
                                <p>{{ $reservation->cafeTable?->name ?? 'Meja belum ditentukan' }}</p>
                            </div>

                            <dl class="grid gap-x-5 gap-y-3 text-sm text-gray-600 sm:grid-cols-2">
                                <div>
                                    <dt class="font-semibold text-primary">Kode Reservasi</dt>
                                    <dd>{{ $reservation->reservation_code }}</dd>
                                </div>
                                <div>
                                    <dt class="font-semibold text-primary">Kode Pembayaran DP</dt>
                                    <dd>{{ $downPayment?->payment_code ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-semibold text-primary">Nominal DP</dt>
                                    <dd>Rp {{ number_format((float) $downPayment?->amount, 0, ',', '.') }}</dd>
                                </div>
                                <div>
                                    <dt class="font-semibold text-primary">Status</dt>
                                    <dd>{{ $downPayment?->status?->label() ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-semibold text-primary">Status Pelunasan</dt>
                                    <dd>{{ $reservation->settlementStatusLabel() }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="font-semibold text-primary">Isi QR</dt>
                                    <dd class="font-mono text-base text-primary">{{ $downPayment?->payment_code ?? '-' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="flex flex-col items-center gap-3 rounded-md border border-gray-100 bg-gray-50 p-4">
                            <div
                                class="flex h-[240px] w-[240px] items-center justify-center rounded-md bg-white shadow-sm"
                                data-admin-payment-qr
                                data-qr-value="{{ $downPayment?->payment_code ?? '' }}"
                            >
                                <span class="text-xs text-gray-400">Memuat QR...</span>
                            </div>
                            <p class="max-w-[240px] text-center text-xs text-gray-500">
                                Scan QR ini untuk mendapatkan kode pembayaran DP: {{ $downPayment?->payment_code ?? '-' }}.
                            </p>
                        </div>
                    </div>
                </article>
            @endif
        </section>

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
            <script>
                window.addEventListener('load', function() {
                    document.querySelectorAll('[data-admin-payment-qr]').forEach(function(node) {
                        const value = node.dataset.qrValue || '';

                        if (!value || typeof window.QRCode === 'undefined') {
                            node.innerHTML = '<span class="text-xs text-gray-400">QR tidak tersedia.</span>';

                            return;
                        }

                        node.innerHTML = '';

                        new window.QRCode(node, {
                            text: value,
                            width: 240,
                            height: 240,
                            colorDark: '#6f4b32',
                            colorLight: '#ffffff',
                            correctLevel: window.QRCode.CorrectLevel.M,
                        });
                    });
                });
            </script>
        @endpush

        @include('guest.components.site-footer')
    </div>
</x-layouts.app>
