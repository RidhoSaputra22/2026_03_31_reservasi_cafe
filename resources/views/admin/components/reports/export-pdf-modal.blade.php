@props([
    'id',
    'title',
    'description' => null,
    'action',
    'submitLabel' => 'Export PDF',
    'size' => 'lg',
])

<x-ui.modal :id="$id" :title="$title" :size="$size">
    @if($description)
        <p class="mb-5 text-sm leading-6 text-base-content/70">{{ $description }}</p>
    @endif

    <form method="GET" action="{{ $action }}" target="_blank" class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2">
            {{ $slot }}
        </div>

        <div class="rounded-2xl border border-base-300/70 bg-base-200/40 p-4 text-sm leading-6 text-base-content/70">
            Filter yang diisi akan dipakai untuk menyusun file PDF historis. Biarkan kosong jika ingin menampilkan seluruh data.
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-base-300/60 pt-4 sm:flex-row sm:items-center sm:justify-end">
            <x-ui.button type="ghost" :isSubmit="false" onclick="document.getElementById('{{ $id }}').close()">
                Tutup
            </x-ui.button>
            <x-ui.button type="primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16V4m0 12-4-4m4 4 4-4M4 20h16" />
                </svg>
                {{ $submitLabel }}
            </x-ui.button>
        </div>
    </form>
</x-ui.modal>
