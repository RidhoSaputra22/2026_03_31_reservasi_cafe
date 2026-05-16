<div x-data="globalSearch()" @click.away="open = false" @keydown.escape.window="open = false">
    <div class="relative">
        <div class="hidden sm:block">
            <div class="w-80">
                <x-ui.input
                    name="global_search_desktop"
                    placeholder="Cari reservasi, menu, meja..."
                    size="sm"
                    class="pr-8"
                    x-model="query"
                    @input.debounce.300ms="search()"
                    @focus="if (results.length) open = true"
                    @keydown.ctrl.k.window.prevent="$el.focus()"
                    @keydown.meta.k.window.prevent="$el.focus()"
                    @keydown.arrow-down.prevent="moveDown()"
                    @keydown.arrow-up.prevent="moveUp()"
                    @keydown.enter.prevent="goToSelected()"
                />
            </div>
            <template x-if="!loading">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute right-2.5 top-2.5 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                </svg>
            </template>
            <template x-if="loading">
                <span class="loading loading-spinner loading-xs absolute right-2.5 top-2.5 text-primary"></span>
            </template>
        </div>

        <button type="button" class="btn btn-ghost btn-circle btn-sm sm:hidden" @click="open = !open" aria-label="Buka pencarian">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
            </svg>
        </button>

        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="fixed inset-x-3 top-20 z-50 sm:absolute sm:top-full sm:right-0 sm:left-auto sm:mt-2 sm:w-96">
            <x-ui.card compact class="overflow-visible">
                <div class="max-h-80 overflow-y-auto" x-ref="resultsList">
                    <div class="sm:hidden">
                        <x-ui.input
                            name="global_search_mobile"
                            placeholder="Cari data admin..."
                            size="sm"
                            class="w-full pr-8 mb-0"
                            x-model="query"
                            @input.debounce.300ms="search()"
                            @keydown.arrow-down.prevent="moveDown()"
                            @keydown.arrow-up.prevent="moveUp()"
                            @keydown.enter.prevent="goToSelected()"
                        />
                    </div>

                    <template x-if="results.length === 0 && query.length >= 2 && !loading">
                        <div class="p-4 text-center text-base-content/60">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 9.75h.01M14.25 9.75h.01M9.75 15.25c1.5-1 3-1 4.5 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <p class="text-sm">Tidak ada hasil untuk "<span x-text="query" class="font-semibold"></span>"</p>
                        </div>
                    </template>

                    <template x-for="(result, index) in results" :key="index">
                        <a :href="result.url"
                            class="flex items-center gap-3 px-4 py-2.5 hover:bg-base-200 cursor-pointer transition-colors border-b border-base-200 last:border-b-0"
                            :class="{ 'bg-base-200': selectedIndex === index }" @mouseenter="selectedIndex = index">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                                <span x-html="getIcon(result.icon)" class="text-primary w-4 h-4"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium truncate" x-text="result.title"></p>
                                <p class="text-xs text-base-content/60 truncate" x-text="result.subtitle"></p>
                            </div>
                            <x-ui.badge type="ghost" size="xs" x-text="result.category" />
                        </a>
                    </template>
                </div>

                <template x-if="results.length > 0">
                    <div class="px-4 py-2 border-t border-base-200 bg-base-200/30">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-base-content/50"><span x-text="results.length"></span> hasil</span>
                            <div class="hidden items-center gap-1 text-xs text-base-content/50 sm:flex">
                                <kbd class="kbd kbd-xs">Ctrl</kbd>
                                <kbd class="kbd kbd-xs">K</kbd>
                            </div>
                        </div>
                    </div>
                </template>
            </x-ui.card>
        </div>
    </div>
</div>
