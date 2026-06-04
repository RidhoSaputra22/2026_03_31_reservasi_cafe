@php
    $editingSlot = $editingSlot ?? null;
    $editingSlotStartTime = $editingSlot?->start_time ? substr($editingSlot->start_time, 0, 5) : '';
    $editingSlotEndTime = $editingSlot?->end_time ? substr($editingSlot->end_time, 0, 5) : '';
@endphp

@extends('admin.layouts.app', ['title' => 'Panel Rentang Jam Reservasi', 'breadcrumbs' => [['label' => 'Rentang Jam Reservasi']]])

@section('header')
    <x-layouts.page-header title="Panel Rentang Jam Reservasi" description="Atur rentang jam operasional per hari. User tetap memilih jam mulai sendiri selama masih berada di dalam rentang aktif.">
        <x-slot:actions>
            <x-ui.button href="#form-slot" type="primary" size="sm" :isSubmit="false">
                {{ $editingSlot ? 'Edit Rentang' : 'Tambah Rentang' }}
            </x-ui.button>
        </x-slot:actions>
    </x-layouts.page-header>
@endsection

@section('content')
    <div class="grid gap-6 xl:grid-cols-[.85fr_1.15fr]">
        <x-ui.card id="form-slot" :title="$editingSlot ? 'Edit Rentang Jam Reservasi' : 'Tambah Rentang Jam Baru'">
            <form method="POST"
                action="{{ $editingSlot ? route('admin.slots.update', $editingSlot) : route('admin.slots.store') }}"
                class="space-y-4">
                @csrf
                @if ($editingSlot)
                    @method('PATCH')
                @endif

                <x-ui.input name="name" label="Label Rentang" :value="$editingSlot?->name"
                    placeholder="Pagi Santai, Lunch, Sore" required
                    helpText="Label ini hanya membantu admin mengenali rentang aktif, bukan pilihan slot tetap di sisi tamu." />
                <x-ui.select name="day_of_week" label="Hari" :options="$dayOptions"
                    :selected="$editingSlot?->day_of_week" placeholder="Pilih hari" required searchable />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="start_time" type="time" label="Mulai" :value="$editingSlotStartTime" required />
                    <x-ui.input name="end_time" type="time" label="Selesai" :value="$editingSlotEndTime" required />
                </div>
                <x-ui.checkbox name="is_active" :checked="$editingSlot?->is_active ?? true" singleLabel="Rentang aktif untuk reservasi" />
                <div class="flex flex-wrap items-center justify-end gap-3">
                    @if ($editingSlot)
                        <x-ui.button href="{{ route('admin.slots.index') }}#form-slot" type="ghost" size="sm"
                            :isSubmit="false">
                            Batal Edit
                        </x-ui.button>
                    @endif
                    <x-ui.button type="primary">
                        {{ $editingSlot ? 'Simpan Perubahan' : 'Simpan Rentang' }}
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card title="Preview Rentang Mingguan">
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($slots->getCollection()->groupBy('day_of_week') as $day => $daySlots)
                    <div class="rounded-box border border-base-200 p-4">
                        <p class="font-bold">{{ collect($dayOptions)->firstWhere('value', (int) $day)['label'] ?? 'Hari '.$day }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($daySlots as $slot)
                                <x-ui.badge :type="$slot->is_active ? 'primary' : 'ghost'" size="sm">
                                    {{ $slot->name }} · {{ substr($slot->start_time, 0, 5) }}-{{ substr($slot->end_time, 0, 5) }}
                                </x-ui.badge>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    </div>

    <x-ui.card class="mt-6">
        <x-ui.data-table
            title="Daftar Rentang Jam"
            :data="$slots"
            :only="['name', 'day_of_week', 'start_time', 'end_time', 'is_active']"
            :labels="['day_of_week' => 'Hari', 'start_time' => 'Mulai', 'end_time' => 'Selesai', 'is_active' => 'Aktif']"
            :formats="['start_time' => 'time', 'end_time' => 'time', 'is_active' => 'boolean']"
            :sortable="['day_of_week', 'start_time', 'end_time', 'is_active']"
            :selectable="false"
            :edit-route="fn ($row) => route('admin.slots.index', ['edit' => $row->id]).'#form-slot'"
            :delete-route="fn ($row) => route('admin.slots.destroy', $row)"
        >
            <x-slot:filters>
                <x-ui.select name="day_of_week" :options="$dayOptions" :selected="request('day_of_week')" placeholder="Semua hari" size="sm" class="min-w-48" />
            </x-slot:filters>
        </x-ui.data-table>
    </x-ui.card>
@endsection
