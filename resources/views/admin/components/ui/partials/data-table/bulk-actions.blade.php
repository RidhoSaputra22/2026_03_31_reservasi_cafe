@if ($selectable && $bulkActionRoute && $items->count() > 0)
    <form
        method="POST"
        action="{{ $bulkActionRoute }}"
        class="w-full sm:w-auto sm:flex flex-wrap sm:flex-nowrap gap-2  items-center"
        @submit.prevent="submitBulkAction($event)"
        x-ref="bulkActionForm"
    >
        @csrf

        <x-ui.select
            name="bulk_action"
            size="sm"
            placeholder=""
            x-model="bulkAction"
            class="w-full mb-2 sm:mb-0 sm:min-w-[200px] "
        >
            <option value="">Bulk Action</option>
            <option value="delete">Hapus</option>

            @isset($bulkActions)
                {{ $bulkActions }}
            @endisset
        </x-ui.select>

        <template x-for="id in selected" :key="id">
            <x-ui.input type="hidden" name="ids[]" x-bind:value="id" />
        </template>

        <x-ui.button
            type="error"
            size="sm"
            x-bind:disabled="!canSubmitBulk"
            x-bind:class="{ 'btn-disabled': !canSubmitBulk }"
        >
            Terapkan
        </x-ui.button>

        <x-ui.button
            type="ghost"
            size="sm"
            :isSubmit="false"
            x-show="selected.length > 0"
            @click="clearSelection()"
        >
            Reset Pilihan
        </x-ui.button>
    </form>

    {{-- Confirm Modal --}}
    <dialog id="bulk-action-confirm-modal" class="modal" x-ref="bulkActionModal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">Konfirmasi Bulk Action</h3>

            <p class="py-3 text-sm text-base-content/70" x-text="confirmMessage"></p>

            <div class="rounded-box bg-base-200 p-3 text-sm">
                <div class="flex items-center justify-between">
                    <span>Aksi</span>
                    <span class="font-semibold" x-text="bulkActionLabel"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Jumlah data</span>
                    <span class="font-semibold" x-text="selected.length"></span>
                </div>
            </div>

            <div class="modal-action">
                <form method="dialog">
                    <x-ui.button type="ghost" :isSubmit="false" @click="closeBulkActionModal()">
                        Batal
                    </x-ui.button>
                </form>

                <x-ui.button
                    type="primary"
                    :isSubmit="false"
                    x-bind:class="bulkAction === 'delete' ? 'btn-error' : 'btn-primary'"
                    @click="confirmBulkAction()"
                >
                    Ya, Lanjutkan
                </x-ui.button>
            </div>
        </div>

        <form method="dialog" class="modal-backdrop">
            <x-ui.button class="hidden" @click="closeBulkActionModal()">close</x-ui.button>
        </form>
    </dialog>
@endif
