<x-layouts.guest title="Login Admin">
    <main class="min-h-screen w-full p-4 sm:p-6 lg:p-8">
        <div class="mx-auto grid min-h-[calc(100vh-4rem)] max-w-6xl overflow-hidden rounded-md border border-base-300/70 bg-base-100/80 shadow-2xl lg:grid-cols-[0.95fr_1.05fr]">
            <aside class="relative hidden overflow-hidden bg-primary p-10 text-primary-content lg:flex lg:flex-col lg:justify-between">
                <div class="absolute -left-24 top-16 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute -bottom-28 right-0 h-80 w-80 rounded-full bg-accent/30 blur-3xl"></div>

                <div class="relative">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary-content/15 text-primary-content">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xl font-bold tracking-wide">AMIKOSPACE</p>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] opacity-75">Admin Cafe</p>
                        </div>
                    </div>

                    <div class="mt-16 max-w-md">
                        <x-ui.badge type="ghost" class="border-primary-content/25 text-primary-content">Back Office</x-ui.badge>
                        <h1 class="mt-6 text-5xl font-bold leading-tight">Panel kendali reservasi cafe.</h1>
                        <p class="mt-5 text-base leading-7 text-primary-content/75">
                            Masuk sebagai admin atau staff untuk mengelola reservasi, pembayaran, menu, meja, dan profil cafe.
                        </p>
                    </div>
                </div>

                <div class="relative grid grid-cols-3 gap-3">
                    <div class="rounded-2xl border border-primary-content/15 bg-primary-content/10 p-4 backdrop-blur">
                        <p class="text-2xl font-black">24h</p>
                        <p class="mt-1 text-xs font-semibold opacity-75">monitoring</p>
                    </div>
                    <div class="rounded-2xl border border-primary-content/15 bg-primary-content/10 p-4 backdrop-blur">
                        <p class="text-2xl font-black">3</p>
                        <p class="mt-1 text-xs font-semibold opacity-75">role akses</p>
                    </div>
                    <div class="rounded-2xl border border-primary-content/15 bg-primary-content/10 p-4 backdrop-blur">
                        <p class="text-2xl font-black">1</p>
                        <p class="mt-1 text-xs font-semibold opacity-75">panel terpadu</p>
                    </div>
                </div>
            </aside>

            <section class="flex items-center justify-center p-6 sm:p-10">
                <div class="w-full max-w-md">
                    <div class="mb-6 text-center lg:hidden">
                        <p class="text-2xl font-bold tracking-wide text-base-content">AMIKOSPACE</p>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-base-content/50">Admin Cafe</p>
                    </div>

                    <x-ui.card class="border-base-300/70">
                        <div class="mb-6">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-primary">Admin Portal</p>
                            <h2 class="mt-2 text-3xl font-bold text-base-content">Login Admin</h2>
                            <p class="mt-2 text-sm leading-6 text-base-content/60">Gunakan email atau username admin/staff.</p>
                        </div>

                        @if ($errors->any())
                            <x-ui.alert type="error" class="mb-5">
                                <div>
                                    <p class="font-semibold">Login belum berhasil</p>
                                    <ul class="mt-1 list-disc space-y-1 pl-5 text-sm">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </x-ui.alert>
                        @endif

                        <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-4">
                            @csrf

                            <x-ui.input name="login" label="Email atau Username" placeholder="admin@amikospace.test" :required="true" />
                            <x-ui.input name="password" label="Password" type="password" placeholder="Password akun admin" :required="true" />

                            <div class="flex items-center justify-between gap-4">
                                <x-ui.checkbox name="remember" :checked="old('remember')">
                                    <span class="text-sm text-base-content/70">Ingat perangkat ini</span>
                                </x-ui.checkbox>
                                <a href="{{ route('login') }}" class="text-sm font-semibold text-primary hover:underline">Login user</a>
                            </div>

                            <x-ui.button type="primary" class="w-full">Masuk ke Dashboard</x-ui.button>
                        </form>
                    </x-ui.card>

                    <p class="mt-6 text-center text-sm text-base-content/60">
                        Akun admin dibuat melalui panel pengguna oleh admin yang sudah aktif.
                    </p>
                </div>
            </section>
        </div>
    </main>
</x-layouts.guest>
