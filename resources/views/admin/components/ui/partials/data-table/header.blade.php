{{-- Header + Filter selalu tampil --}}
<div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
    <div class="space-y-1">
        <h2 class="card-title">{{ $title }}</h2>

        <div class="text-sm text-base-content/70">
            Total:
            <span class="font-semibold">
                {{ $isPaginated ? $totalItems : $items->count() }}
            </span>

            @if ($selectable)
                <span class="mx-2">•</span>
                <span>
                    Dipilih:
                    <span class="font-semibold" x-text="selected.length"></span>
                </span>
            @endif
        </div>
    </div>

    <form method="GET" action="{{ route(Route::currentRouteName()) }}#{{ Str::slug($title) }}" class="flex flex-col gap-2 lg:flex-row lg:items-center">
        <div class="relative w-full sm:min-w-64 flex-1">
            <x-heroicon-s-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/60 z-10 w-4 h-4" />

            <x-ui.input
                size="sm"
                name="search"
                :value="request('search')"
                class="pl-9"
                placeholder="Cari data..."
            />
        </div>

        @isset($filters)
            {{ $filters }}
        @endisset

        <x-ui.button type="primary" size="sm">Filter</x-ui.button>
        <x-ui.button type="ghost" size="sm" :href="url()->current()" :isSubmit="false">Reset</x-ui.button>
    </form>
</div>
