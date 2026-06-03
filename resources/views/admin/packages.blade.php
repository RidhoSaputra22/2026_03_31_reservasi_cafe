@php
    $editingPackage = $editingPackage ?? null;
    $aliasesValue = $editingPackage ? implode(PHP_EOL, $editingPackage->aliases ?? []) : '';
    $facilitiesValue = $editingPackage ? implode(PHP_EOL, $editingPackage->facilities ?? []) : '';
    $notesValue = $editingPackage ? implode(PHP_EOL, $editingPackage->notes ?? []) : '';
@endphp

@extends('admin.layouts.app', ['title' => 'Panel Paket Reservasi', 'breadcrumbs' => [['label' => 'Paket Reservasi']]])

@section('header')
    <x-layouts.page-header
        title="Panel Paket Reservasi"
        description="Buat paket reservasi sendiri, atur harga dasar, durasi awal yang termasuk, dan biaya tambahan per jam.">
        <x-slot:actions>
            <x-ui.button href="#form-paket" type="primary" size="sm" :isSubmit="false">
                {{ $editingPackage ? 'Edit Paket' : 'Tambah Paket' }}
            </x-ui.button>
        </x-slot:actions>
    </x-layouts.page-header>
@endsection

@section('content')
    <div class="grid gap-6 xl:grid-cols-[.95fr_1.05fr]">
        <x-ui.card id="form-paket" :title="$editingPackage ? 'Edit Paket Reservasi' : 'Tambah Paket Reservasi'">
            <form method="POST"
                action="{{ $editingPackage ? route('admin.packages.update', $editingPackage) : route('admin.packages.store') }}"
                class="space-y-4">
                @csrf
                @if ($editingPackage)
                    @method('PATCH')
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="name" label="Nama Paket" :value="$editingPackage?->name" placeholder="Contoh: Coffee Date Corner" required />
                    <x-ui.input name="slug" label="Slug URL" :value="$editingPackage?->slug"
                        placeholder="Otomatis dari nama jika dikosongkan" helpText="Dipakai untuk URL halaman booking." />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="category" label="Kategori" :value="$editingPackage?->category"
                        placeholder="Date Night, Work Space, Community" required />
                    <x-ui.input name="image_path" label="Path Gambar" :value="$editingPackage?->image_path"
                        placeholder="assets/images/hero.jpg" />
                </div>

                <x-ui.textarea name="summary" label="Ringkasan Singkat" rows="3" :value="$editingPackage?->summary"
                    placeholder="Deskripsi pendek untuk kartu paket" required />
                <x-ui.textarea name="description" label="Deskripsi Lengkap" rows="5" :value="$editingPackage?->description"
                    placeholder="Penjelasan lengkap paket untuk halaman booking" required />

                <div class="grid gap-4 sm:grid-cols-3">
                    <x-ui.input name="base_price" type="number" label="Harga Dasar" :value="$editingPackage?->base_price"
                        placeholder="150000" required />
                    <x-ui.input name="included_hours" type="number" label="Jam Termasuk"
                        :value="$editingPackage?->included_hours ?? 1" placeholder="2" required />
                    <x-ui.input name="extra_hour_price" type="number" label="Biaya Tambahan per Jam"
                        :value="$editingPackage?->extra_hour_price ?? 0" placeholder="50000" required />
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <x-ui.textarea name="aliases_text" label="Alias Slug" rows="4" :value="$aliasesValue"
                        placeholder="Satu alias per baris atau pisahkan dengan koma" />
                    <x-ui.textarea name="facilities_text" label="Fasilitas Paket" rows="4" :value="$facilitiesValue"
                        placeholder="Satu fasilitas per baris" />
                    <x-ui.textarea name="notes_text" label="Catatan Paket" rows="4" :value="$notesValue"
                        placeholder="Satu catatan per baris" />
                </div>

                <div class="grid gap-4 sm:grid-cols-[180px_1fr_1fr]">
                    <x-ui.input name="sort_order" type="number" label="Urutan Tampil"
                        :value="$editingPackage?->sort_order ?? 0" placeholder="0" />
                    <x-ui.checkbox name="is_featured" :checked="$editingPackage?->is_featured ?? false"
                        singleLabel="Tampilkan sebagai paket unggulan" />
                    <x-ui.checkbox name="is_active" :checked="$editingPackage?->is_active ?? true"
                        singleLabel="Paket aktif di website" />
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3">
                    @if ($editingPackage)
                        <x-ui.button href="{{ route('admin.packages.index') }}#form-paket" type="ghost" size="sm"
                            :isSubmit="false">
                            Batal Edit
                        </x-ui.button>
                    @endif
                    <x-ui.button type="primary">
                        {{ $editingPackage ? 'Simpan Perubahan' : 'Simpan Paket' }}
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card title="Ringkasan Paket">
            <div class="stats stats-vertical w-full bg-base-200 sm:stats-horizontal">
                <div class="stat">
                    <div class="stat-title">Total Paket</div>
                    <div class="stat-value text-lg">{{ $packages->count() }}</div>
                    <div class="stat-desc">Tersimpan di katalog reservasi</div>
                </div>
                <div class="stat">
                    <div class="stat-title">Paket Aktif</div>
                    <div class="stat-value text-lg">{{ $activeCount }}</div>
                    <div class="stat-desc">Sedang tampil untuk tamu</div>
                </div>
                <div class="stat">
                    <div class="stat-title">Paket Unggulan</div>
                    <div class="stat-value text-lg">{{ $featuredCount }}</div>
                    <div class="stat-desc">Diprioritaskan di halaman depan</div>
                </div>
            </div>

            <div class="divider"></div>

            <div class="space-y-4">
                <form method="GET" action="{{ route('admin.packages.index') }}" class="grid gap-3 sm:grid-cols-[1fr_220px_140px]">
                    <x-ui.input name="search" label="Cari Paket" :value="request('search')" placeholder="Nama, kategori, slug" />
                    <x-ui.select name="status" label="Status" :options="$statusOptions" :selected="request('status')"
                        placeholder="Semua status" />
                    <div class="flex items-end gap-3">
                        <x-ui.button type="primary" size="sm">Filter</x-ui.button>
                        <x-ui.button href="{{ route('admin.packages.index') }}" type="ghost" size="sm" :isSubmit="false">Reset</x-ui.button>
                    </div>
                </form>

                <div class="flex flex-wrap gap-2">
                    @forelse ($categoryOptions as $category)
                        <x-ui.badge type="ghost" size="sm">{{ $category['label'] }}</x-ui.badge>
                    @empty
                        <p class="text-sm text-base-content/60">Kategori akan muncul setelah paket pertama dibuat.</p>
                    @endforelse
                </div>
            </div>
        </x-ui.card>
    </div>

    <x-ui.card class="mt-6" title="Daftar Paket Reservasi">
        <div class="grid gap-4 lg:grid-cols-2">
            @forelse ($packages as $package)
                <div class="rounded-box border border-base-200 p-5">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="space-y-2">
                            <div class="flex flex-wrap gap-2">
                                <x-ui.badge :type="$package->is_active ? 'success' : 'ghost'" size="sm">
                                    {{ $package->is_active ? 'Aktif' : 'Nonaktif' }}
                                </x-ui.badge>
                                @if ($package->is_featured)
                                    <x-ui.badge type="warning" size="sm">Unggulan</x-ui.badge>
                                @endif
                                <x-ui.badge type="primary" size="sm">{{ $package->category }}</x-ui.badge>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold">{{ $package->name }}</h3>
                                <p class="text-sm text-base-content/60">/{{ $package->slug }}</p>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <x-ui.button href="{{ route('admin.packages.index', ['edit' => $package->id]) }}#form-paket"
                                type="ghost" size="sm" :isSubmit="false">
                                Edit
                            </x-ui.button>
                            <form method="POST" action="{{ route('admin.packages.destroy', $package) }}"
                                onsubmit="return confirm('Hapus paket ini?')">
                                @csrf
                                @method('DELETE')
                                <x-ui.button type="error" size="sm">Hapus</x-ui.button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-4 space-y-2 text-sm text-base-content/75">
                        <p>{{ $package->summary }}</p>
                        <p>
                            Harga dasar
                            <span class="font-semibold text-base-content">Rp {{ number_format((float) $package->base_price, 0, ',', '.') }}</span>,
                            termasuk
                            <span class="font-semibold text-base-content">{{ $package->included_hours }} jam</span>,
                            tambahan
                            <span class="font-semibold text-base-content">Rp {{ number_format((float) $package->extra_hour_price, 0, ',', '.') }}/jam</span>.
                        </p>
                    </div>

                    <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                        <div class="rounded-box bg-base-200 p-3">
                            <p class="font-semibold">Fasilitas</p>
                            <p class="mt-1 text-base-content/70">
                                {{ collect($package->facilities ?? [])->implode(', ') ?: 'Belum diisi.' }}
                            </p>
                        </div>
                        <div class="rounded-box bg-base-200 p-3">
                            <p class="font-semibold">Catatan</p>
                            <p class="mt-1 text-base-content/70">
                                {{ collect($package->notes ?? [])->implode(', ') ?: 'Belum diisi.' }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-box border border-dashed border-base-300 p-8 text-center text-base-content/60 lg:col-span-2">
                    Belum ada paket reservasi. Tambahkan paket pertama dari form di atas.
                </div>
            @endforelse
        </div>
    </x-ui.card>
@endsection
