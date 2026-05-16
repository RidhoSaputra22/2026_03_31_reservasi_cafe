{{--
    Nested FAB Navigation

    @param array $items - Navigation items: label, href, icon, active?, buttonClass?
    @param string $label - Title for the navigation block
    @param string $description - Helper text for the navigation block
    @param string $buttonClass - Classes for the navigation toggle icon surface
    @param string $panelClass - Classes for the navigation panel container
    @param string $itemButtonClass - Classes for default navigation buttons
    @param string $activeItemButtonClass - Classes for active navigation buttons
--}}

@props([
    'items' => [],
    'label' => 'Navigasi cepat',
    'description' => 'Pindah ke modul utama tanpa menutup konteks kerja sekarang.',
    'buttonClass' => 'btn btn-circle btn-sm btn-neutral shadow-md',
    'panelClass' => 'rounded-md border border-dashed border-base-300/80 bg-base-200/60 p-2.5',
    'itemButtonClass' => 'btn btn-circle btn-sm btn-base-100 shadow-sm',
    'activeItemButtonClass' => 'btn btn-circle btn-sm btn-primary shadow-sm',
])

@if (!empty($items))
    <div x-data="{ navOpen: false }" @fab-close.window="navOpen = false" class="{{ $panelClass }}">
        <button type="button" @click="navOpen = !navOpen" :aria-expanded="navOpen.toString()"
            class="flex w-full items-center justify-between gap-3 rounded-xl px-2 py-2 text-left transition hover:bg-base-100/70 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/25">
            <span class="flex min-w-0 items-center gap-3">
                <span class="{{ $buttonClass }} pointer-events-none shrink-0">
                    <x-ui.fab.icon name="dashboard" class="h-4 w-4" />
                </span>

                <span class="min-w-0">
                    <span class="block truncate text-sm font-semibold text-base-content">{{ $label }}</span>
                    <span class="mt-1 block text-xs leading-relaxed text-base-content/60">{{ $description }}</span>
                </span>
            </span>

            <span class="btn btn-ghost btn-circle btn-sm shrink-0 transition-transform duration-200"
                :class="{ 'rotate-45': navOpen }" aria-hidden="true">
                <x-ui.fab.icon name="plus" class="h-4 w-4" />
            </span>
        </button>

        <div x-cloak x-show="navOpen" x-transition.origin.bottom.right.duration.200ms
            class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
            @foreach ($items as $item)
                @php
                    $resolvedItemButtonClass = $item['buttonClass']
                        ?? (($item['active'] ?? false) ? $activeItemButtonClass : $itemButtonClass);
                    $resolvedCardClass = ($item['active'] ?? false)
                        ? 'border-primary/30 bg-primary/10 text-primary'
                        : 'border-base-300/70 bg-base-100/85 text-base-content hover:border-base-300';
                @endphp

                <a href="{{ $item['href'] }}" x-on:click="$dispatch('fab-close')"
                    aria-label="{{ $item['label'] }}"
                    class="group flex flex-col items-center gap-2 rounded-xl border px-2 py-3 text-center transition duration-200 hover:-translate-y-0.5 hover:shadow-sm {{ $resolvedCardClass }}">
                    <span class="{{ $resolvedItemButtonClass }} pointer-events-none">
                        <x-ui.fab.icon :name="$item['icon']" class="h-4 w-4" />
                    </span>

                    <span class="text-[11px] font-medium leading-tight">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
@endif
