<x-modal open="midtransLoadingModalOpen" onClose="preventMidtransLoadingClose()" maxWidth="max-w-md"
    wrapperClass="relative flex min-h-full items-center justify-center px-4 py-6"
    panelClass="rounded-md bg-white px-6 py-8 text-center shadow-2xl" overlayClass="bg-black/60 backdrop-blur-sm"
    :showCloseButton="false" :hideHeader="true">
    <div class="flex flex-col items-center gap-4">
        <span class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-primary">
            <span class="h-10 w-10 animate-spin rounded-full border-4 border-primary/20 border-t-primary"></span>
        </span>

        <div class="space-y-2">
            <h2 class="text-xl font-semibold text-primary" x-text="midtransLoadingTitle"></h2>
            <p class="text-sm leading-6 text-gray-500" x-text="midtransLoadingMessage"></p>
        </div>

        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-400">
            Mohon tunggu sebentar
        </p>
    </div>
</x-modal>
