<x-layouts.app>
    <div>
        @include('guest.components.site-navbar')

        <section class="min-h-screen px-6 py-20 md:px-12">
            <div class="mx-auto flex max-w-4xl flex-col gap-6 rounded-[2rem] border border-gray-100 bg-white p-8 shadow-sm">
                <div class="space-y-2">
                    <p class="text-sm uppercase tracking-[0.3em] text-primary/60">Cart</p>
                    <h1 class="text-4xl font-semibold text-primary">Keranjang reservasi belum tersedia.</h1>
                </div>
                <p class="text-base font-light text-gray-600">
                    Untuk saat ini proses reservasi Cafe Amiko masih diarahkan langsung dari halaman detail paket ke form booking.
                </p>
                <div>
                    <a href="{{ route('packages.index') }}"
                        class="inline-flex rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-primary/90">
                        Kembali ke Reservasi
                    </a>
                </div>
            </div>
        </section>

        @include('guest.components.site-footer')
    </div>
</x-layouts.app>
