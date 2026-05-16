{{-- Empty state, tapi filter tetap sudah tampil di atas --}}
@if ($items->count() == 0)
    <div class="text-center py-12 text-base-content/50">
            <x-heroicon-o-inbox class="h-12 w-12 mx-auto mb-4" />

            {{-- Tampilkan pesan berbeda jika ada filter aktif --}}


        <div class="text-lg font-semibold">
            {{ $hasActiveFilter ? 'Data tidak ditemukan' : 'Tidak ada data' }}
        </div>
        <div class="text-sm mt-1">
            {{ $hasActiveFilter ? 'Coba ubah atau reset filter Anda.' : $emptyText }}
        </div>

        @if ($hasActiveFilter)
            <div class="mt-4">
                <a href="{{ url()->current() }}" class="btn btn-sm btn-ghost">
                    Reset Filter
                </a>
            </div>
        @endif
    </div>

@endif
