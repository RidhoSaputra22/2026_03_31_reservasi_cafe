@php
    $tugas = request()->route('tugas');
    $navigationItems = [
        [
            'label' => 'Dashboard',
            'href' => route('dashboard'),
            'icon' => 'dashboard',
            'active' => request()->routeIs('dashboard'),
        ],
        [
            'label' => 'Jadwal Kuliah',
            'href' => route('mata-kuliah.index'),
            'icon' => 'mata-kuliah',
            'active' => request()->routeIs('mata-kuliah.*'),
        ],
        [
            'label' => 'Tugas',
            'href' => route('tugas.index'),
            'icon' => 'tugas',
            'active' => request()->routeIs('tugas.*') || request()->routeIs('todo.*'),
        ],
        [
            'label' => 'Kalender',
            'href' => route('kalender.index'),
            'icon' => 'kalender',
            'active' => request()->routeIs('kalender.*') || request()->routeIs('events.*'),
        ],
        [
            'label' => 'Statistik',
            'href' => route('statistik.index'),
            'icon' => 'statistik',
            'active' => request()->routeIs('statistik.*'),
        ],
    ];

    $fab = null;

    if (request()->routeIs('dashboard')) {
        $fab = [
            'variant' => 'flower',
            'triggerIcon' => 'dashboard',
            'triggerLabel' => 'Buka navigasi dashboard',
            'triggerClass' => 'btn btn-circle btn-lg btn-primary shadow-xl',
            'mainActionClass' => 'fab-main-action btn btn-circle btn-lg btn-primary shadow-xl',
            'items' => [
                [
                    'label' => 'Jadwal Kuliah',
                    'href' => route('mata-kuliah.index'),
                    'icon' => 'mata-kuliah',
                    'buttonClass' => 'btn btn-circle btn-lg btn-secondary shadow-lg',
                ],
                [
                    'label' => 'Tugas',
                    'href' => route('tugas.index'),
                    'icon' => 'tugas',
                    'buttonClass' => 'btn btn-circle btn-lg btn-warning shadow-lg',
                ],
                [
                    'label' => 'Kalender',
                    'href' => route('kalender.index'),
                    'icon' => 'kalender',
                    'buttonClass' => 'btn btn-circle btn-lg btn-info shadow-lg',
                ],
                [
                    'label' => 'Statistik',
                    'href' => route('statistik.index'),
                    'icon' => 'statistik',
                    'buttonClass' => 'btn btn-circle btn-lg btn-accent shadow-lg',
                ],
            ],
        ];
    } elseif (request()->routeIs('mata-kuliah.show')) {
        $fab = null;
    } elseif (request()->routeIs('mata-kuliah.*')) {
        $fab = [
            'variant' => '',
            'triggerIcon' => 'mata-kuliah',
            'triggerLabel' => 'Buka aksi jadwal kuliah',
            'triggerClass' => 'btn btn-circle btn-lg btn-secondary shadow-xl',
            'mainActionClass' => 'fab-main-action btn btn-circle btn-lg btn-secondary shadow-xl',
            'items' => [
                [
                    'label' => 'Tambah Mata Kuliah',
                    'href' => route('mata-kuliah.create'),
                    'icon' => 'plus',
                    'buttonClass' => 'btn btn-circle btn-lg btn-primary shadow-lg',
                ],
                [
                    'label' => 'Import Mata Kuliah',
                    'href' => route('import-export.import', 'mata-kuliah'),
                    'icon' => 'import',
                    'buttonClass' => 'btn btn-circle btn-lg btn-info shadow-lg',
                ],
                [
                    'label' => 'Export Mata Kuliah',
                    'href' => route('import-export.export', 'mata-kuliah'),
                    'icon' => 'export',
                    'buttonClass' => 'btn btn-circle btn-lg btn-success shadow-lg',
                ],
                [
                    'label' => 'Download Template',
                    'href' => route('import-export.template', 'mata-kuliah'),
                    'icon' => 'template',
                    'buttonClass' => 'btn btn-circle btn-lg btn-accent shadow-lg',
                ],
            ],
        ];
    } elseif (request()->routeIs('tugas.show') && $tugas) {
        $fab = [
            'variant' => '',
            'triggerIcon' => 'tugas',
            'triggerLabel' => 'Buka aksi detail tugas',
            'triggerClass' => 'btn btn-circle btn-lg btn-warning shadow-xl',
            'mainActionClass' => 'fab-main-action btn btn-circle btn-lg btn-warning shadow-xl',
            'items' => [
                [
                    'label' => 'Kembali ke Daftar Tugas',
                    'href' => route('tugas.index'),
                    'icon' => 'back',
                    'buttonClass' => 'btn btn-circle btn-lg btn-neutral shadow-lg',
                ],
                [
                    'label' => 'Edit Tugas',
                    'href' => route('tugas.edit', $tugas),
                    'icon' => 'edit',
                    'buttonClass' => 'btn btn-circle btn-lg btn-primary shadow-lg',
                ],
                [
                    'label' => 'Tambah Checklist',
                    'href' => route('todo.create', ['tugas_id' => $tugas->id]),
                    'icon' => 'checklist',
                    'buttonClass' => 'btn btn-circle btn-lg btn-secondary shadow-lg',
                ],
                [
                    'label' => 'Buka Kalender',
                    'href' => route('kalender.index'),
                    'icon' => 'kalender',
                    'buttonClass' => 'btn btn-circle btn-lg btn-info shadow-lg',
                ],
            ],
        ];
    } elseif (request()->routeIs('tugas.*')) {
        $fab = [
            'variant' => '',
            'triggerIcon' => 'tugas',
            'triggerLabel' => 'Buka aksi tugas',
            'triggerClass' => 'btn btn-circle btn-lg btn-warning shadow-xl',
            'mainActionClass' => 'fab-main-action btn btn-circle btn-lg btn-warning shadow-xl',
            'items' => [
                [
                    'label' => 'Tambah Tugas',
                    'href' => route('tugas.create'),
                    'icon' => 'plus',
                    'buttonClass' => 'btn btn-circle btn-lg btn-primary shadow-lg',
                ],
                [
                    'label' => 'Import Tugas',
                    'href' => route('import-export.import', 'tugas'),
                    'icon' => 'import',
                    'buttonClass' => 'btn btn-circle btn-lg btn-info shadow-lg',
                ],
                [
                    'label' => 'Export Tugas',
                    'href' => route('import-export.export', 'tugas'),
                    'icon' => 'export',
                    'buttonClass' => 'btn btn-circle btn-lg btn-success shadow-lg',
                ],
                [
                    'label' => 'Buka Kalender',
                    'href' => route('kalender.index'),
                    'icon' => 'kalender',
                    'buttonClass' => 'btn btn-circle btn-lg btn-accent shadow-lg',
                ],
            ],
        ];
    } elseif (request()->routeIs('kalender.*')) {
        $fab = [
            'variant' => '',
            'triggerIcon' => 'kalender',
            'triggerLabel' => 'Buka aksi kalender',
            'triggerClass' => 'btn btn-circle btn-lg btn-info shadow-xl',
            'mainActionClass' => 'fab-main-action btn btn-circle btn-lg btn-info shadow-xl',
            'items' => [
                [
                    'label' => 'Tambah Event',
                    'href' => route('events.create'),
                    'icon' => 'plus',
                    'buttonClass' => 'btn btn-circle btn-lg btn-primary shadow-lg',
                ],
                [
                    'label' => 'Kelola Event',
                    'href' => route('events.index'),
                    'icon' => 'list',
                    'buttonClass' => 'btn btn-circle btn-lg btn-secondary shadow-lg',
                ],
                [
                    'label' => 'Tambah Tugas',
                    'href' => route('tugas.create'),
                    'icon' => 'tugas',
                    'buttonClass' => 'btn btn-circle btn-lg btn-warning shadow-lg',
                ],
                [
                    'label' => 'Jadwal Kuliah',
                    'href' => route('mata-kuliah.index'),
                    'icon' => 'mata-kuliah',
                    'buttonClass' => 'btn btn-circle btn-lg btn-accent shadow-lg',
                ],
            ],
        ];
    } elseif (request()->routeIs('events.*')) {
        $fab = [
            'variant' => '',
            'triggerIcon' => 'kalender',
            'triggerLabel' => 'Buka aksi event',
            'triggerClass' => 'btn btn-circle btn-lg btn-info shadow-xl',
            'mainActionClass' => 'fab-main-action btn btn-circle btn-lg btn-info shadow-xl',
            'items' => [
                [
                    'label' => 'Buka Kalender',
                    'href' => route('kalender.index'),
                    'icon' => 'kalender',
                    'buttonClass' => 'btn btn-circle btn-lg btn-primary shadow-lg',
                ],
                [
                    'label' => 'Tambah Event',
                    'href' => route('events.create'),
                    'icon' => 'plus',
                    'buttonClass' => 'btn btn-circle btn-lg btn-secondary shadow-lg',
                ],
                [
                    'label' => 'Daftar Event',
                    'href' => route('events.index'),
                    'icon' => 'list',
                    'buttonClass' => 'btn btn-circle btn-lg btn-accent shadow-lg',
                ],
                [
                    'label' => 'Tambah Tugas',
                    'href' => route('tugas.create'),
                    'icon' => 'tugas',
                    'buttonClass' => 'btn btn-circle btn-lg btn-warning shadow-lg',
                ],
            ],
        ];
    } elseif (request()->routeIs('statistik.*')) {
        $fab = [
            'variant' => '',
            'triggerIcon' => 'statistik',
            'triggerLabel' => 'Buka aksi statistik',
            'triggerClass' => 'btn btn-circle btn-lg btn-accent shadow-xl',
            'mainActionClass' => 'fab-main-action btn btn-circle btn-lg btn-accent shadow-xl',
            'panelTitle' => 'Pusat kontrol statistik',
            'panelDescription' => 'Lompat ke bagian penting di halaman ini atau pindah ke modul lain tanpa kehilangan konteks.',
            'items' => [
                [
                    'label' => 'Ringkasan utama',
                    'description' => 'Lihat kartu total tugas dan skor produktivitas.',
                    'href' => route('statistik.index') . '#summary-stats',
                    'icon' => 'dashboard',
                    'buttonClass' => 'btn btn-circle btn-lg btn-primary shadow-lg',
                ],
                [
                    'label' => 'Grafik analisis',
                    'description' => 'Buka distribusi prioritas, status, dan aktivitas mingguan.',
                    'href' => route('statistik.index') . '#chart-overview',
                    'icon' => 'statistik',
                    'buttonClass' => 'btn btn-circle btn-lg btn-accent shadow-lg',
                ],
                [
                    'label' => 'Deadline terdekat',
                    'description' => 'Fokus ke daftar deadline 14 hari ke depan.',
                    'href' => route('statistik.index') . '#deadline-timeline',
                    'icon' => 'kalender',
                    'buttonClass' => 'btn btn-circle btn-lg btn-info shadow-lg',
                ],
                [
                    'label' => 'Progress tugas',
                    'description' => 'Tinjau rasio todo dan progress keseluruhan.',
                    'href' => route('statistik.index') . '#overall-progress',
                    'icon' => 'tugas',
                    'buttonClass' => 'btn btn-circle btn-lg btn-warning shadow-lg',
                ],
            ],
        ];
    }
@endphp

@if ($fab && !empty($fab['items']))
    <div class="h-24 sm:h-28" aria-hidden="true"></div>

    <x-ui.fab :trigger-aria-label="$fab['triggerLabel']" :main-action-aria-label="'Tutup menu aksi cepat'"
        :trigger-class="$fab['triggerClass']" :main-action-class="$fab['mainActionClass']"
        :panel-title="$fab['panelTitle'] ?? 'Aksi cepat'"
        :panel-description="$fab['panelDescription'] ?? 'Pilih aksi yang paling relevan untuk halaman yang sedang Anda buka.'">
        <x-slot:trigger>
            <x-ui.fab.icon :name="$fab['triggerIcon']" class="h-6 w-6" />
        </x-slot:trigger>

        <x-slot:mainAction>
            <x-ui.fab.icon name="close" class="h-6 w-6" />
        </x-slot:mainAction>



        @foreach ($fab['items'] as $item)
            <x-ui.fab.item :tooltip="$item['label']" :description="$item['description'] ?? null"
                :href="$item['href']" :button-class="$item['buttonClass']" :active="$item['active'] ?? false">
                <x-ui.fab.icon :name="$item['icon']" class="h-5 w-5" />
            </x-ui.fab.item>
        @endforeach

        @if (!request()->routeIs('dashboard'))
            <x-ui.fab.nested-nav :items="$navigationItems" />
        @endif

    </x-ui.fab>
@endif
