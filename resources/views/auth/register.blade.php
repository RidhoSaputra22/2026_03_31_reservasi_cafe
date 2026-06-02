<x-layouts.app>
    <main class="min-h-screen flex items-center justify-center bg-white">
        <section class="w-full max-w-5xl grid grid-cols-2 shadow-xl rounded-md overflow-hidden">

           <div class="flex-1 bg-coffee-900 w-full  relative" style="background-image: url('{{ asset('assets/images/hero.jpg') }}'); background-size: cover; background-position: center;">

                <div class="p-10">
                    <div class="absolute  z-10 space-y-3">
                        <h1 class="text-coffee-100 text-4xl font-semibold  tracking-wide ">
                            Daftar sekali, reservasi lebih cepat.
                        </h1>
                        <p class="text-coffee-100 text-sm font-light"> Buat akun pelanggan sederhana untuk menyimpan identitas reservasi dan mempercepat pemesanan meja.</p>
                    </div>
                    <img src="{{ asset('assets/images/coffee-cup.png') }}" class="h-14 absolute bottom-4 right-7 opacity-90 z-10" alt="Coffee Cup">
                </div>
                <div class="absolute top-0  bg-black absolute h-full w-full opacity-50"></div>


            </div>

            <div class="bg-white mx-auto w-full p-7 sm:p-9 ">
                <div class="mb-8">
                    <p class="text-xs font-black uppercase tracking-[0.28em] text-coffee-400">AMIKOSPACE</p>
                    <h2 class="mt-3 text-3xl font-light tracking-wide text-black">Registrasi Pelanggan</h2>
                    <p class="mt-2 text-sm leading-6 text-coffee-600">Isi data singkat untuk membuat akun pelanggan.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <p class="font-black text-red-900">Registrasi belum berhasil</p>
                        <ul class="mt-2 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
                    @csrf
                    @if (request('redirect'))
                        <input type="hidden" name="redirect_to" value="{{ request('redirect') }}">
                    @endif

                    <div>
                        <label for="name" class="mb-2 block text-sm font-black text-coffee-900">Nama</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}"
                            autocomplete="name" required class="form-field" placeholder="Nama lengkap">
                    </div>

                    <div>
                        <label for="email" class="mb-2 block text-sm font-black text-coffee-900">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}"
                            autocomplete="email" required class="form-field" placeholder="nama@email.com">
                    </div>

                    <div>
                        <label for="phone_number" class="mb-2 block text-sm font-black text-coffee-900">Nomor
                            WhatsApp</label>
                        <input id="phone_number" name="phone_number" type="tel" value="{{ old('phone_number') }}"
                            autocomplete="tel" class="form-field" placeholder="08xxxxxxxxxx">
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="password" class="mb-2 block text-sm font-black text-coffee-900">Password</label>
                            <input id="password" name="password" type="password" autocomplete="new-password" required
                                class="form-field" placeholder="Minimal 8 karakter">
                        </div>
                        <div>
                            <label for="password_confirmation"
                                class="mb-2 block text-sm font-black text-coffee-900">Konfirmasi</label>
                            <input id="password_confirmation" name="password_confirmation" type="password"
                                autocomplete="new-password" required class="form-field" placeholder="Ulangi password">
                        </div>
                    </div>

                    <button type="submit" class="pill-button-dark w-full">Daftar</button>
                </form>

                <p class="mt-6 text-center text-sm text-coffee-600">
                    Sudah punya akun?
                    <a href="{{ route('login', request('redirect') ? ['redirect' => request('redirect')] : []) }}"
                        class="font-black text-black underline-offset-4 hover:underline">Masuk di sini</a>
                </p>
            </div>
        </section>
    </main>
</x-layouts.app>
