@extends('admin.layouts.app', ['title' => 'Panel Meja', 'breadcrumbs' => [['label' => 'Meja & Area']]])

@section('header')
    <x-layouts.page-header title="Panel Meja & Area" description="Kelola kode meja, kapasitas, lokasi, status, dan ketersediaan meja cafe.">
        <x-slot:actions>
            <x-ui.button href="#form-meja" type="primary" size="sm" :isSubmit="false">Tambah Meja</x-ui.button>
            <x-ui.button type="ghost" size="sm" :isSubmit="false" onclick="status_meja_help.showModal()">Panduan Status</x-ui.button>
        </x-slot:actions>
    </x-layouts.page-header>
@endsection

@section('content')
    <div class="grid gap-6 xl:grid-cols-[.85fr_1.15fr]">
        <x-ui.card id="form-meja" title="Tambah Meja Baru">
            <form method="POST" action="{{ route('admin.tables.store') }}" class="space-y-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="code" label="Kode Meja" placeholder="A1" required />
                    <x-ui.input name="name" label="Nama Meja" placeholder="Meja A1" required />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="capacity" type="number" label="Kapasitas" placeholder="4" required />
                    <x-ui.select name="status" label="Status" :options="$statusOptions" selected="available" required />
                </div>
                <x-ui.input name="location" label="Area" placeholder="Indoor, Outdoor, Window Area" />
                <x-ui.textarea name="description" label="Catatan" rows="3" placeholder="Contoh: dekat stop kontak, sofa, smoking area" />
                <x-ui.checkbox name="is_active" :checked="true" singleLabel="Meja aktif untuk reservasi" />
                <div class="flex justify-end">
                    <x-ui.button type="primary">Simpan Meja</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card title="Peta Kapasitas Cepat">
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($tables->getCollection()->take(6) as $table)
                    @php
                        $type = match ($table->status) {
                            App\Enums\TableStatus::Available => 'success',
                            App\Enums\TableStatus::Reserved => 'warning',
                            App\Enums\TableStatus::Occupied => 'error',
                            default => 'neutral',
                        };
                    @endphp
                    <div class="rounded-box border border-base-200 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-bold">{{ $table->code }} · {{ $table->name }}</p>
                                <p class="text-sm text-base-content/60">{{ $table->capacity }} kursi · {{ $table->location ?: 'Tanpa area' }}</p>
                            </div>
                            <x-ui.badge :type="$type" size="sm">{{ $table->status->label() }}</x-ui.badge>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    </div>

    <x-ui.card class="mt-6">
        <x-ui.data-table
            title="Daftar Meja"
            :data="$tables"
            :only="['code', 'name', 'capacity', 'status', 'location', 'is_active']"
            :labels="['is_active' => 'Aktif']"
            :formats="['status' => 'badge', 'is_active' => 'boolean']"
            :sortable="['code', 'name', 'capacity', 'status', 'location', 'is_active']"
            :selectable="false"
            :delete-route="fn ($row) => route('admin.tables.destroy', $row)"
        >
            <x-slot:filters>
                <x-ui.select name="status" :options="$statusOptions" :selected="request('status')" placeholder="Semua status" size="sm" class="min-w-48" />
            </x-slot:filters>
        </x-ui.data-table>
    </x-ui.card>

    <x-ui.modal id="status_meja_help" title="Panduan Status Meja" size="lg">
        <div class="space-y-3 text-sm leading-6 text-base-content/75">
            <p><strong>Tersedia</strong> dipakai untuk meja yang dapat dipilih sistem reservasi.</p>
            <p><strong>Dipesan</strong> menandakan meja punya reservasi aktif yang belum check-in.</p>
            <p><strong>Terisi</strong> dipakai saat tamu sudah check-in dan meja sedang digunakan.</p>
            <p><strong>Selesai Digunakan</strong> bisa dipakai sebagai status transisi sebelum meja dikembalikan ke tersedia.</p>
        </div>
    </x-ui.modal>
@endsection
