{{--
    Page Header Component
    Reusable page header with title, description, and action buttons.

    Usage:
    <x-layouts.page-header title="Data Penduduk" description="Kelola data penduduk kelurahan">
        <x-slot:actions>
            <x-ui.button href="/penduduk/create">Tambah Penduduk</x-ui.button>
        </x-slot:actions>
    </x-layouts.page-header>
--}}

@props([
    'title',
    'description' => null,
])

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class=" text-2xl font-bold text-base-content">{{ $title }}</h2>
        @if($description)
            <p class="text-base-content/60 mt-1">{{ $description }}</p>
        @endif
    </div>
    @if(isset($actions))
        <div class="flex flex-wrap items-center gap-2 justify-between">
            {{ $actions }}
        </div>
    @endif
</div>
