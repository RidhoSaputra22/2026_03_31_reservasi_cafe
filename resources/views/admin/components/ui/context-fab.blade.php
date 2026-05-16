@php
    $navigationItems = [
        [
            'label' => 'Dashboard',
            'href' => route('dashboard'),
            'icon' => 'dashboard',
            'active' => request()->routeIs('dashboard'),
        ],
        [
            'label' => 'Reservasi',
            'href' => route('admin.reservations.index'),
            'icon' => 'calendar',
            'active' => request()->routeIs('admin.reservations.*'),
        ],
        [
            'label' => 'Menu',
            'href' => route('admin.menu.index'),
            'icon' => 'menu',
            'active' => request()->routeIs('admin.menu.*'),
        ],
        [
            'label' => 'Meja',
            'href' => route('admin.tables.index'),
            'icon' => 'table',
            'active' => request()->routeIs('admin.tables.*'),
        ],
        [
            'label' => 'Pembayaran',
            'href' => route('admin.payments.index'),
            'icon' => 'payment',
            'active' => request()->routeIs('admin.payments.*'),
        ],
        [
            'label' => 'Profil',
            'href' => route('admin.profile.index'),
            'icon' => 'store',
            'active' => request()->routeIs('admin.profile.*'),
        ],
    ];

    $items = match (true) {
        request()->routeIs('dashboard') => [
            [
                'label' => 'Lihat Reservasi Hari Ini',
                'description' => 'Fokus ke booking dan tamu yang datang hari ini.',
                'href' => route('admin.reservations.index', ['date' => now()->toDateString()]),
                'icon' => 'calendar',
                'buttonClass' => 'btn btn-circle btn-lg btn-primary shadow-lg',
            ],
            [
                'label' => 'Tambah Menu Baru',
                'description' => 'Masuk ke panel menu untuk menambah item cafe.',
                'href' => route('admin.menu.index') . '#form-menu',
                'icon' => 'plus',
                'buttonClass' => 'btn btn-circle btn-lg btn-success shadow-lg',
            ],
            [
                'label' => 'Cek Pembayaran',
                'description' => 'Pantau pembayaran pending dan verifikasi.',
                'href' => route('admin.payments.index'),
                'icon' => 'payment',
                'buttonClass' => 'btn btn-circle btn-lg btn-warning shadow-lg',
            ],
        ],
        request()->routeIs('admin.reservations.*') => [
            [
                'label' => 'Pembayaran',
                'description' => 'Buka panel pembayaran reservasi.',
                'href' => route('admin.payments.index'),
                'icon' => 'payment',
                'buttonClass' => 'btn btn-circle btn-lg btn-warning shadow-lg',
            ],
            [
                'label' => 'Slot Reservasi',
                'description' => 'Kelola slot waktu yang dapat dipilih pelanggan.',
                'href' => route('admin.slots.index'),
                'icon' => 'clock',
                'buttonClass' => 'btn btn-circle btn-lg btn-info shadow-lg',
            ],
        ],
        request()->routeIs('admin.menu.*') => [
            [
                'label' => 'Tambah Menu',
                'description' => 'Lompat ke form menu baru.',
                'href' => route('admin.menu.index') . '#form-menu',
                'icon' => 'plus',
                'buttonClass' => 'btn btn-circle btn-lg btn-success shadow-lg',
            ],
            [
                'label' => 'Profil Cafe',
                'description' => 'Atur informasi cafe yang menjadi induk menu.',
                'href' => route('admin.profile.index'),
                'icon' => 'store',
                'buttonClass' => 'btn btn-circle btn-lg btn-primary shadow-lg',
            ],
        ],
        request()->routeIs('admin.tables.*') => [
            [
                'label' => 'Tambah Meja',
                'description' => 'Lompat ke form master meja dan area.',
                'href' => route('admin.tables.index') . '#form-meja',
                'icon' => 'plus',
                'buttonClass' => 'btn btn-circle btn-lg btn-success shadow-lg',
            ],
            [
                'label' => 'Reservasi',
                'description' => 'Lihat dampak meja terhadap booking aktif.',
                'href' => route('admin.reservations.index'),
                'icon' => 'calendar',
                'buttonClass' => 'btn btn-circle btn-lg btn-primary shadow-lg',
            ],
        ],
        default => [
            [
                'label' => 'Dashboard',
                'description' => 'Kembali ke ringkasan operasional cafe.',
                'href' => route('dashboard'),
                'icon' => 'dashboard',
                'buttonClass' => 'btn btn-circle btn-lg btn-primary shadow-lg',
            ],
            [
                'label' => 'Lihat Website',
                'description' => 'Buka halaman pelanggan AMIKOSPACE.',
                'href' => route('landing'),
                'icon' => 'external',
                'buttonClass' => 'btn btn-circle btn-lg btn-neutral shadow-lg',
            ],
        ],
    };
@endphp

@if (!empty($items))
    <div class="h-24 sm:h-28" aria-hidden="true"></div>

    <x-ui.fab trigger-aria-label="Buka aksi cepat admin" main-action-aria-label="Tutup aksi cepat"
        trigger-class="btn btn-circle btn-lg btn-primary shadow-xl" main-action-class="fab-main-action btn btn-circle btn-lg btn-neutral shadow-xl"
        panel-title="Aksi cepat" panel-description="Pindah panel atau lakukan tugas admin yang paling sering dipakai.">
        <x-slot:trigger>
            <x-ui.fab.icon name="dashboard" class="h-6 w-6" />
        </x-slot:trigger>

        <x-slot:mainAction>
            <x-ui.fab.icon name="close" class="h-6 w-6" />
        </x-slot:mainAction>

        @foreach ($items as $item)
            <x-ui.fab.item :tooltip="$item['label']" :description="$item['description'] ?? null"
                :href="$item['href']" :button-class="$item['buttonClass']" :active="$item['active'] ?? false">
                <x-ui.fab.icon :name="$item['icon']" class="h-5 w-5" />
            </x-ui.fab.item>
        @endforeach

        <x-ui.fab.nested-nav :items="$navigationItems" label="Navigasi panel" description="Pindah ke modul admin utama." />
    </x-ui.fab>
@endif
