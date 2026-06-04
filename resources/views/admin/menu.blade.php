@php
    $editingMenuItem = $editingMenuItem ?? null;
@endphp

@extends('admin.layouts.app', ['title' => 'Panel Menu', 'breadcrumbs' => [['label' => 'Menu Cafe']]])

@section('header')
    <x-layouts.page-header title="Panel Menu Cafe" description="Tambah, pantau, dan hapus item menu yang ditampilkan sebagai katalog cafe.">
        <x-slot:actions>
            <x-ui.button href="#form-menu" type="primary" size="sm" :isSubmit="false">
                {{ $editingMenuItem ? 'Edit Menu' : 'Tambah Menu' }}
            </x-ui.button>
        </x-slot:actions>
    </x-layouts.page-header>
@endsection

@section('content')
    <div class="grid gap-6 xl:grid-cols-[.85fr_1.15fr]">
        <x-ui.card id="form-menu" :title="$editingMenuItem ? 'Edit Menu Cafe' : 'Tambah Menu Baru'">
            <form method="POST"
                action="{{ $editingMenuItem ? route('admin.menu.update', $editingMenuItem) : route('admin.menu.store') }}"
                class="space-y-4">
                @csrf
                @if ($editingMenuItem)
                    @method('PATCH')
                @endif

                <x-ui.input name="name" label="Nama Menu" :value="$editingMenuItem?->name"
                    placeholder="Contoh: Signature Latte" required />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="category" label="Kategori" :value="$editingMenuItem?->category"
                        placeholder="Coffee, Food, Dessert" />
                    <x-ui.input name="price" type="number" label="Harga" :value="$editingMenuItem?->price"
                        placeholder="38000" required />
                </div>
                <x-ui.textarea name="description" label="Deskripsi" rows="4" :value="$editingMenuItem?->description"
                    placeholder="Deskripsi singkat menu" />
                <x-ui.checkbox name="is_available" :checked="$editingMenuItem?->is_available ?? true" singleLabel="Menu tersedia" />
                <div class="flex flex-wrap items-center justify-end gap-3">
                    @if ($editingMenuItem)
                        <x-ui.button href="{{ route('admin.menu.index') }}#form-menu" type="ghost" size="sm"
                            :isSubmit="false">
                            Batal Edit
                        </x-ui.button>
                    @endif
                    <x-ui.button type="primary">
                        {{ $editingMenuItem ? 'Simpan Perubahan' : 'Simpan Menu' }}
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card title="Kategori Aktif">
            <div class="flex flex-wrap gap-2">
                @forelse ($categories as $category)
                    <x-ui.badge type="ghost" class="px-4 py-3">{{ $category }}</x-ui.badge>
                @empty
                    <p class="text-sm text-base-content/60">Belum ada kategori menu.</p>
                @endforelse
            </div>
            <div class="divider"></div>
            <div class="stats stats-vertical w-full bg-base-200 sm:stats-horizontal">
                <div class="stat">
                    <div class="stat-title">Cafe</div>
                    <div class="stat-value text-lg">{{ $profile->name }}</div>
                    <div class="stat-desc">Induk data menu</div>
                </div>
                <div class="stat">
                    <div class="stat-title">DP Reservasi</div>
                    <div class="stat-value text-lg">Rp {{ number_format((float) $profile->down_payment_amount, 0, ',', '.') }}</div>
                    <div class="stat-desc">Dikelola di profil cafe</div>
                </div>
            </div>
        </x-ui.card>
    </div>

    <x-ui.card class="mt-6">
        <x-ui.data-table
            title="Daftar Menu"
            :data="$menuItems"
            :only="['name', 'category', 'price', 'is_available']"
            :labels="['is_available' => 'Tersedia']"
            :formats="['price' => 'money', 'is_available' => 'boolean']"
            :sortable="['name', 'category', 'price', 'is_available']"
            :selectable="false"
            :edit-route="fn ($row) => route('admin.menu.index', ['edit' => $row->id]).'#form-menu'"
            :delete-route="fn ($row) => route('admin.menu.destroy', $row)"
        />
    </x-ui.card>
@endsection
