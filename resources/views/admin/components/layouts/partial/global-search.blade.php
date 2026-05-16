{{-- Search Desktop --}}
<div class="" x-data="globalSearch()" @click.away="open = false"
    @keydown.escape.window="open = false">
    <div class="relative  ">
        <div class="hidden sm:block">
            <div class="w-86">
                <x-ui.input
                name="global_search_desktop"
                placeholder="Cari... (Ctrl+K)"
                size="sm"
                class="pr-8"
                x-model="query"
                @input.debounce.300ms="search()"
                @focus="if (results.length) open = true"
                @keydown.ctrl.k.window.prevent="$el.focus()"
                @keydown.arrow-down.prevent="moveDown()"
                @keydown.arrow-up.prevent="moveUp()"
                @keydown.enter.prevent="goToSelected()"
                />
            </div>
            <template x-if="!loading">
                <x-heroicon-o-magnifying-glass class="h-4 w-4 absolute right-2.5 top-2.5 text-base-content/40" />
            </template>
            <template x-if="loading">
                <span class="loading loading-spinner loading-xs absolute right-2.5 top-2.5 text-primary"></span>
            </template>
        </div>
        <div class="block sm:hidden">
            {{-- Search Mobile --}}
                <x-heroicon-o-magnifying-glass class="h-6 w-6 " @click="open = !open" />

        </div>
        <div x-show="open" x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="fixed inset-0  flex justify-center items-start mt-20 w-full z-50 sm:absolute sm:top-full sm:right-0 sm:left-auto sm:mt-2 sm:w-96">
            <x-ui.card compact class="w-96 overflow-visible">
                <div class="max-h-80 overflow-y-auto" x-ref="resultsList">
                    <div>
                        <x-ui.input
                            name="global_search_mobile"
                            placeholder="Cari... (Ctrl+K)"
                            size="sm"
                            class="w-full pr-8 sm:hidden mb-0"
                            x-model="query"
                            @input.debounce.300ms="search()"
                            @focus="if (results.length) open = true"
                            @keydown.ctrl.k.window.prevent="$el.focus()"
                            @keydown.arrow-down.prevent="moveDown()"
                            @keydown.arrow-up.prevent="moveUp()"
                            @keydown.enter.prevent="goToSelected()"
                        />
                    </div>
                    <template x-if="results.length === 0 && query.length >= 2 && !loading">
                        <div class="p-4 text-center text-base-content/60">
                            <x-heroicon-o-face-frown class="h-8 w-8 mx-auto mb-2 opacity-40" />
                            <p class="text-sm">Tidak ada hasil untuk "<span x-text="query" class="font-semibold"></span>"
                            </p>
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
                            <span class="text-xs text-base-content/50"><span x-text="results.length"></span> hasil
                                ditemukan</span>
                            <div class="flex items-center gap-1 text-xs text-base-content/50">
                                <kbd class="kbd kbd-xs">&uarr;</kbd>
                                <kbd class="kbd kbd-xs">&darr;</kbd>
                                <span>navigasi</span>
                                <kbd class="kbd kbd-xs">Enter</kbd>
                                <span>pilih</span>
                            </div>
                        </div>
                    </div>
                </template>
            </x-ui.card>
        </div>
    </div>
</div>
