{{--
    Top navigation bar for AMIKOSPACE admin.
--}}

@props(['title' => 'Dashboard'])

@php($adminUser = auth()->user())

<header class="navbar bg-base-100 shadow-sm sticky top-0 z-30 px-4 lg:px-6">
    <div class="flex justify-center items-center gap-2">
        <x-ui.button type="ghost" size="sm" :isSubmit="false" class="btn-square lg:hidden"
            @click="sidebarMobileOpen = true">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </x-ui.button>

        <div>
            <h1 class="text-lg font-semibold text-base-content truncate w-36 sm:w-auto">{{ $title }}</h1>
            <p class="hidden text-xs text-base-content/50 sm:block">Back office reservasi cafe</p>
        </div>
    </div>

    <div class="flex-1"></div>

    <div class="flex-none flex items-center gap-2">
        <x-layouts.partial.global-search />

        <button type="button" class="btn btn-ghost btn-circle btn-sm" title="Ganti tema"
            :aria-label="adminTheme === 'dark' ? 'Gunakan tema terang' : 'Gunakan tema gelap'"
            @click="toggleTheme()">
            <svg x-show="adminTheme === 'dark'" x-cloak xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 fill-current" viewBox="0 0 24 24">
                <path d="M5.64 17l-.71.71a1 1 0 0 0 1.41 1.41l.71-.71A1 1 0 0 0 5.64 17ZM5 12a1 1 0 0 0-1-1H3a1 1 0 0 0 0 2h1a1 1 0 0 0 1-1Zm7-7a1 1 0 0 0 1-1V3a1 1 0 0 0-2 0v1a1 1 0 0 0 1 1Zm5.66 2.34a1 1 0 0 0 .7-.29l.72-.71a1 1 0 0 0-1.42-1.42l-.71.72a1 1 0 0 0 .71 1.7ZM17 18.36l.71.72a1 1 0 0 0 1.42-1.42l-.72-.71A1 1 0 0 0 17 18.36ZM21 11h-1a1 1 0 0 0 0 2h1a1 1 0 0 0 0-2ZM6.34 4.93A1 1 0 0 0 4.93 6.34l.71.71a1 1 0 0 0 1.41-1.41l-.71-.71ZM12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10Zm0 8a3 3 0 1 1 0-6 3 3 0 0 1 0 6Zm0 4a1 1 0 0 0-1 1v1a1 1 0 0 0 2 0v-1a1 1 0 0 0-1-1Z" />
            </svg>
            <svg x-show="adminTheme !== 'dark'" xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 fill-current" viewBox="0 0 24 24">
                <path d="M21.64 13a1 1 0 0 0-1.05-.14 8.05 8.05 0 0 1-3.37.73 8.15 8.15 0 0 1-8.14-8.1 8.59 8.59 0 0 1 .25-2A1 1 0 0 0 8 2.36 10.14 10.14 0 1 0 22 14.05a1 1 0 0 0-.36-1.05Z" />
            </svg>
        </button>

        <div class="dropdown dropdown-end">
            <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                <x-ui.avatar :name="$adminUser?->name ?? 'Admin Cafe'" size="sm" />
                <span class="hidden sm:inline">{{ $adminUser?->role?->label() ?? 'Admin' }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </label>
            <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box w-80 shadow-lg mt-2 p-2">
                <li class="menu-title">
                    <span class="text-xs text-base-content/60">{{ $adminUser?->email ?? 'admin@amikospace.test' }}</span>
                </li>
                <li>
                    <a href="{{ route('admin.profile.index') }}" class="flex gap-3 items-center justify-start">
                        <x-ui.fab.icon name="store" class="h-4 w-4" />
                        Profil Cafe
                    </a>
                </li>
                <li>
                    <a href="{{ route('landing') }}" class="flex gap-3 items-center justify-start">
                        <x-ui.fab.icon name="external" class="h-4 w-4" />
                        Lihat Website
                    </a>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex gap-3 items-center justify-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H3m4-4-4 4 4 4m5-12h5a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-5" />
                            </svg>
                            <p>Logout</p>
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

@pushOnce('scripts')
    <script>
        function globalSearch() {
            return {
                query: '',
                results: [],
                loading: false,
                open: false,
                selectedIndex: -1,
                abortController: null,

                async search() {
                    if (this.query.length < 2) {
                        this.results = [];
                        this.open = false;
                        return;
                    }

                    if (this.abortController) {
                        this.abortController.abort();
                    }

                    this.loading = true;
                    this.abortController = new AbortController();

                    try {
                        const response = await fetch(
                            `{{ route('admin.global-search') }}?q=${encodeURIComponent(this.query)}`, {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                signal: this.abortController.signal,
                            });

                        if (!response.ok) throw new Error('Search failed');

                        const data = await response.json();
                        this.results = data.results || [];
                        this.open = true;
                        this.selectedIndex = -1;
                    } catch (error) {
                        if (error.name !== 'AbortError') {
                            this.results = [];
                        }
                    } finally {
                        this.loading = false;
                    }
                },

                moveDown() {
                    if (this.selectedIndex < this.results.length - 1) {
                        this.selectedIndex++;
                        this.scrollToSelected();
                    }
                },

                moveUp() {
                    if (this.selectedIndex > 0) {
                        this.selectedIndex--;
                        this.scrollToSelected();
                    }
                },

                scrollToSelected() {
                    this.$nextTick(() => {
                        const list = this.$refs.resultsList;
                        if (!list) return;
                        const items = list.querySelectorAll('a');
                        if (items[this.selectedIndex]) {
                            items[this.selectedIndex].scrollIntoView({ block: 'nearest' });
                        }
                    });
                },

                goToSelected() {
                    if (this.selectedIndex >= 0 && this.results[this.selectedIndex]) {
                        window.location.href = this.results[this.selectedIndex].url;
                    }
                },

                getIcon(name) {
                    const icons = {
                        dashboard: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 0 1 2-2h3v5H4V6Zm11-2h3a2 2 0 0 1 2 2v3h-5V4ZM4 15h5v5H6a2 2 0 0 1-2-2v-3Zm11 0h5v3a2 2 0 0 1-2 2h-3v-5Z"/></svg>',
                        calendar: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"/></svg>',
                        menu: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10"/></svg>',
                        payment: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8h18M5 5h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Zm2 10h4"/></svg>',
                        users: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM4 21a8 8 0 0 1 16 0"/></svg>',
                    };

                    return icons[name] || icons.dashboard;
                }
            };
        }
    </script>
@endPushOnce
