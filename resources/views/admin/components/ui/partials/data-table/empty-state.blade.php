{{-- Empty state, tapi filter tetap sudah tampil di atas --}}
@if ($items->count() == 0)
    <div class="text-center py-12 text-base-content/50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 13v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0-3-8H7l-3 8m16 0h-5l-2 3h-2l-2-3H4" />
            </svg>

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
