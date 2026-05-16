{{--
    Reusable Delete Confirmation Modal Component

    Supports two modes:
    1. Form-based (for Blade delete buttons):
       <x-ui.button @click="$dispatch('confirm-delete', { action: '/url', message: 'Pesan konfirmasi' })">Hapus</x-ui.button>

    2. Promise-based (for JavaScript async code):
       if (!await confirmAction('Hapus data ini?')) return;
--}}

<div
    x-data="{
        action: '',
        message: 'Apakah Anda yakin ingin menghapus data ini?',
        mode: 'form',
        _resolve: null,
        ready: false,

        openForm(detail) {
            if (!this.ready || !detail || !detail.action) return;
            this.action = detail.action;
            this.message = detail.message || 'Apakah Anda yakin ingin menghapus data ini?';
            this.mode = 'form';
            this.$refs.dialog.showModal();
        },

        openPromise(msg) {
            this.message = msg || 'Apakah Anda yakin ingin menghapus data ini?';
            this.mode = 'promise';
            this.$refs.dialog.showModal();
            return new Promise(resolve => { this._resolve = resolve; });
        },

        cancel() {
            this.$refs.dialog.close();
            if (this._resolve) { this._resolve(false); this._resolve = null; }
        },

        confirmed() {
            this.$refs.dialog.close();
            if (this._resolve) { this._resolve(true); this._resolve = null; }
        }
    }"
    @confirm-delete.window="openForm($event.detail)"
    x-init="$nextTick(() => { ready = true; }); window.confirmAction = (msg) => openPromise(msg)"
>
    <dialog x-ref="dialog" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box max-w-sm">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-error/10 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Konfirmasi Hapus</h3>
                    <p class="py-2 text-base-content/70" x-text="message"></p>
                </div>
            </div>
            <div class="modal-action">
                <x-ui.button type="ghost" :isSubmit="false" @click="cancel()">Batal</x-ui.button>

                {{-- Form mode: submit DELETE request --}}
                <form x-show="mode === 'form'" :action="action" method="POST">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="error">Hapus</x-ui.button>
                </form>

                {{-- Promise mode: resolve promise --}}
                <x-ui.button x-show="mode === 'promise'" x-cloak type="error" :isSubmit="false" @click="confirmed()">Hapus</x-ui.button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <x-ui.button class="hidden" @click="cancel()">close</x-ui.button>
        </form>
    </dialog>
</div>
