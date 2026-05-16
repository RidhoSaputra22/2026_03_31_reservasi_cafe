<x-layouts.app title="Panel Pengguna" :breadcrumbs="[['label' => 'Pengguna']]">
    <x-slot:header>
        <x-layouts.page-header title="Panel Pengguna" description="Kelola akun admin, staff cafe, dan pelanggan yang terhubung ke reservasi.">
            <x-slot:actions>
                <x-ui.button href="#form-user" type="primary" size="sm" :isSubmit="false">Tambah Pengguna</x-ui.button>
            </x-slot:actions>
        </x-layouts.page-header>
    </x-slot:header>

    <div class="grid gap-6 xl:grid-cols-[.85fr_1.15fr]">
        <x-ui.card id="form-user" title="Tambah Pengguna Baru">
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf
                <x-ui.input name="name" label="Nama" placeholder="Nama lengkap" required />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="username" label="Username" placeholder="admin_amikospace" />
                    <x-ui.select name="role" label="Role" :options="$roleOptions" selected="staff" required />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="email" type="email" label="Email" placeholder="staff@amikospace.test" required />
                    <x-ui.input name="phone_number" label="Nomor HP" placeholder="08xxxxxxxxxx" />
                </div>
                <x-ui.input name="password" type="password" label="Password" placeholder="Kosongkan untuk default: password" />
                <div class="flex justify-end">
                    <x-ui.button type="primary">Simpan Pengguna</x-ui.button>
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
            :delete-route="fn ($row) => route('admin.users.destroy', $row)"
        >
            <x-slot:filters>
                <x-ui.select name="role" :options="$roleOptions" :selected="request('role')" placeholder="Semua role" size="sm" class="min-w-48" />
            </x-slot:filters>
        </x-ui.data-table>
    </x-ui.card>
</x-layouts.app>
