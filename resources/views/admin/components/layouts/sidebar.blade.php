{{--
    Admin sidebar for AMIKOSPACE cafe reservation back office.
--}}

@php
    $navSections = [
        'Operasional' => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'dashboard'],
            ['label' => 'Reservasi', 'route' => 'admin.reservations.index', 'active' => 'admin.reservations.*', 'icon' => 'calendar'],
            ['label' => 'Pembayaran', 'route' => 'admin.payments.index', 'active' => 'admin.payments.*', 'icon' => 'payment'],
        ],
        'Master Data' => [
            ['label' => 'Menu Cafe', 'route' => 'admin.menu.index', 'active' => 'admin.menu.*', 'icon' => 'menu'],
            ['label' => 'Meja & Area', 'route' => 'admin.tables.index', 'active' => 'admin.tables.*', 'icon' => 'table'],
            ['label' => 'Slot Reservasi', 'route' => 'admin.slots.index', 'active' => 'admin.slots.*', 'icon' => 'clock'],
            ['label' => 'Pengguna', 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'icon' => 'users'],
        ],
        'Cafe' => [
            ['label' => 'Profil Cafe', 'route' => 'admin.profile.index', 'active' => 'admin.profile.*', 'icon' => 'store'],
            ['label' => 'Lihat Website', 'route' => 'landing', 'active' => 'landing', 'icon' => 'external'],
        ],
    ];
@endphp

<aside x-cloak
    class="fixed top-0 left-0 z-50 h-screen w-64 bg-base-100 shadow-lg transition-transform duration-300 overflow-y-auto flex flex-col"
    :class="{
        'translate-x-0': sidebarMobileOpen,
        '-translate-x-full lg:translate-x-0': !sidebarMobileOpen
    }">

    <div class="flex items-center gap-3 px-4 py-5 border-b border-base-200">
        <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center shrink-0 text-primary-content">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
        </div>
        <div class="min-w-0">
            <h2 class="font-bold text-base-content text-sm leading-tight">AMIKOSPACE</h2>
            <p class="text-xs text-base-content/60">Admin Cafe</p>
        </div>
        <x-ui.button type="ghost" size="sm" :isSubmit="false" class="btn-circle ml-auto lg:hidden"
            @click="sidebarMobileOpen = false">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </x-ui.button>
    </div>

    <ul class="menu menu-md px-3 py-4 gap-1 w-full flex-1">
        @foreach ($navSections as $section => $items)
            <li class="menu-title {{ $loop->first ? '' : 'mt-4' }}">
                <span>{{ $section }}</span>
            </li>

            @foreach ($items as $item)
                <li>
                    <a href="{{ route($item['route']) }}" class="{{ request()->routeIs($item['active']) ? 'active' : '' }}">
                        <x-ui.fab.icon :name="$item['icon']" class="h-5 w-5" />
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        @endforeach
    </ul>

    <div class="border-t border-base-200 p-4">
        <div class="rounded-box bg-base-200 p-3 text-xs leading-relaxed text-base-content/70">
            Kelola reservasi, menu, meja, pembayaran, dan profil cafe dari satu panel.
        </div>
    </div>
</aside>
