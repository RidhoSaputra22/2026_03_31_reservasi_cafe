@php
    $canCreateSettlementFromRow = $midtransConfigured && $row->canCreateSettlementFromAdmin();
@endphp

@if ($canCreateSettlementFromRow)
    <form method="POST" action="{{ route('admin.payments.settlement', $row->reservation) }}" x-data="{ creating: false }"
        @submit="creating = true">
        @csrf
        <input type="hidden" name="method" value="{{ \App\Enums\PaymentMethod::Qris->value }}">
        <x-ui.button type="ghost" size="xs" title="Bayar sisa di button ini" aria-label="Bayar sisa di button ini"
            x-bind:disabled="creating">
            <span x-show="!creating">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                </svg>

            </span>
            <span x-cloak x-show="creating">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4 animate-spin" viewBox="0 0 24 24" fill="none"
                    aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M22 12a10 10 0 0 0-10-10v4a6 6 0 0 1 6 6h4Z"></path>
                </svg>
            </span>
        </x-ui.button>
    </form>
@endif

<x-ui.button type="ghost" size="xs" :isSubmit="false" class="text-error" title="Hapus" aria-label="Hapus"
    @click="$dispatch('confirm-delete', { action: '{{ route('admin.payments.destroy', $row) }}', message: 'Apakah Anda yakin ingin menghapus data ini?' })">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 7l-.9 12A2 2 0 0116.1 21H7.9a2 2 0 01-2-1.9L5 7m5 4v6m4-6v6M4 7h16m-3 0V5a2 2 0 00-2-2h-6a2 2 0 00-2 2v2" />
    </svg>
</x-ui.button>
