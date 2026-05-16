<x-ui.modal id="modal-event-form" size="md" :closeButton="true">
   <x-slot:title>
        <div class="flex  items-center gap-2 w-full">
            <x-heroicon-o-calendar-days class="h-8 w-8 text-primary/80" />
            <h3 class=" font-bold text-xl ">Event <span x-text="modalDate"></span></h3>
            {{-- <div class="w-full"></div> --}}

        </div>
    </x-slot:title>

    <div class="space-y-3">
        <div>
            <label class="label label-text text-xs font-medium">Judul <span class="text-error">*</span></label>
            <x-ui.input type="text" class="w-full input-sm" x-model="eventForm.title" placeholder="Nama event" />
            <p x-show="eventFormErrors.title" class="text-error text-xs mt-1" x-text="eventFormErrors.title"></p>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="label label-text text-xs font-medium">Mulai <span class="text-error">*</span></label>
                <x-ui.input type="datetime-local" class="w-full input-sm" x-model="eventForm.start" />
                <p x-show="eventFormErrors.start" class="text-error text-xs mt-1" x-text="eventFormErrors.start"></p>
            </div>
            <div>
                <label class="label label-text text-xs font-medium">Selesai</label>
                <x-ui.input type="datetime-local" class="w-full input-sm" x-model="eventForm.end" />
            </div>
        </div>

        <div>
            <label class="label label-text text-xs font-medium">Lokasi</label>
            <x-ui.input type="text" class="w-full input-sm" x-model="eventForm.location"
                placeholder="Lokasi (opsional)" />
        </div>

        <div>
            <label class="label label-text text-xs font-medium">Deskripsi</label>
            <x-ui.textarea class="w-full textarea-sm" x-model="eventForm.description" rows="2"
                placeholder="Deskripsi (opsional)" />
        </div>

        <div>
            <label class="label label-text text-xs font-medium">Warna Event</label>

            <div class="flex flex-wrap gap-2 mt-2">
                <template x-for="opt in colorOptions" :key="'preview-' + opt.value">
                    <x-ui.badge
                        class="badge-outline gap-2 px-3 py-3"
                        x-bind:class="getColorPreviewClass(opt.value) + (eventForm.color === opt.value ? ' ring-2 ring-offset-1 ring-primary' : '') + ' cursor-pointer'"
                        @click="eventForm.color = opt.value"
                    >
                        <span class="w-2.5 h-2.5 rounded-full bg-current opacity-80"></span>
                        <span x-text="opt.label"></span>
                    </x-ui.badge>
                </template>
            </div>
        </div>

        <div class="modal-action mt-4">
            <x-ui.button class="btn-ghost btn-sm" @click="closeEventForm()">Batal</x-ui.button>
            <x-ui.button class="btn-success btn-sm" @click="submitEventForm()" x-bind:disabled="eventFormLoading">
                <span x-show="eventFormLoading" class="loading loading-spinner loading-xs"></span>
                <span x-text="eventForm.id ? 'Update' : 'Simpan'"></span>
            </x-ui.button>
        </div>
    </div>
</x-ui.modal>
