@php
    $editingUser = $editingUser ?? null;
@endphp

@extends('admin.layouts.app', ['title' => 'Panel Pengguna', 'breadcrumbs' => [['label' => 'Pengguna']]])

@section('header')
    <x-layouts.page-header title="Panel Pengguna" description="Kelola akun admin, staff cafe, dan pelanggan yang terhubung ke reservasi.">
        <x-slot:actions>
            <x-ui.button href="#form-user" type="primary" size="sm" :isSubmit="false">
                {{ $editingUser ? 'Edit Pengguna' : 'Tambah Pengguna' }}
            </x-ui.button>
        </x-slot:actions>
    </x-layouts.page-header>
@endsection

@section('content')
    <div class="grid gap-6 xl:grid-cols-[.85fr_1.15fr]">
        <x-ui.card id="form-user" :title="$editingUser ? 'Edit Pengguna' : 'Tambah Pengguna Baru'">
            <form method="POST"
                action="{{ $editingUser ? route('admin.users.update', $editingUser) : route('admin.users.store') }}"
                class="space-y-4">
                @csrf
                @if ($editingUser)
                    @method('PATCH')
                @endif

                <x-ui.input name="name" label="Nama" :value="$editingUser?->name" placeholder="Nama lengkap" required />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="username" label="Username" :value="$editingUser?->username"
                        placeholder="admin_amikospace" />
                    <x-ui.select name="role" label="Role" :options="$roleOptions"
                        :selected="$editingUser?->role?->value ?? 'staff'" required />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="email" type="email" label="Email" :value="$editingUser?->email"
                        placeholder="staff@amikospace.test" required />
                    <x-ui.input name="phone_number" label="Nomor HP" :value="$editingUser?->phone_number"
                        placeholder="08xxxxxxxxxx" />
                </div>
                <x-ui.input name="password" type="password" label="Password" placeholder="Kosongkan untuk default: password" />
                <div class="flex flex-wrap items-center justify-end gap-3">
                    @if ($editingUser)
                        <x-ui.button href="{{ route('admin.users.index') }}#form-user" type="ghost" size="sm"
                            :isSubmit="false">
                            Batal Edit
                        </x-ui.button>
                    @endif
                    <x-ui.button type="primary">
                        {{ $editingUser ? 'Simpan Perubahan' : 'Simpan Pengguna' }}
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card title="Role Operasional">
            <div class="grid gap-3">
                @foreach ($roleOptions as $role)
                    <div class="rounded-box border border-base-200 p-4">
                        <div class="flex items-center justify-between gap-4">
                            <p class="font-semibold">{{ $role['label'] }}</p>
                            <x-ui.badge type="ghost">{{ $role['value'] }}</x-ui.badge>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    </div>

    <x-ui.card class="mt-6">
        <x-ui.data-table
            title="Daftar Pengguna"
            :data="$users"
            :only="['name', 'username', 'email', 'phone_number', 'role']"
            :labels="['phone_number' => 'Nomor HP']"
            :formats="['role' => 'badge']"
            :sortable="['name', 'email', 'role']"
            :selectable="false"
            :edit-route="fn ($row) => route('admin.users.index', ['edit' => $row->id]).'#form-user'"
            :delete-route="fn ($row) => route('admin.users.destroy', $row)"
        >
            <x-slot:filters>
                <x-ui.select name="role" :options="$roleOptions" :selected="request('role')" placeholder="Semua role" size="sm" class="min-w-48" />
            </x-slot:filters>
        </x-ui.data-table>
    </x-ui.card>
@endsection
