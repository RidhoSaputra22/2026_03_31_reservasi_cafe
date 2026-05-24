@extends('layouts.cafe')

@section('title', 'Registrasi Pelanggan - AMIKOSPACE Coffee & Tea')
@section('description', 'Portal registrasi pelanggan AMIKOSPACE Coffee & Tea.')
@section('active_page', 'login')

@section('content')
<main class="min-h-screen pt-28">
  <section class="relative overflow-hidden px-5 py-12 lg:px-8">
    <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_14%_10%,rgba(187,150,114,0.20),transparent_26rem),linear-gradient(180deg,rgba(255,255,255,0.72),rgba(251,247,240,0.9))]"></div>

    <div class="mx-auto grid max-w-6xl gap-8 lg:grid-cols-[0.9fr_1fr] lg:items-center">
      <div class="hidden lg:block">
        <p class="text-sm font-black uppercase tracking-[0.28em] text-coffee-400">New Member</p>
        <h1 class="mt-4 max-w-xl text-5xl font-light leading-tight tracking-wide text-black">Daftar sekali, reservasi lebih cepat.</h1>
        <p class="mt-5 max-w-lg leading-7 text-coffee-600">
          Buat akun pelanggan sederhana untuk menyimpan identitas reservasi dan mempercepat pemesanan meja.
        </p>
      </div>

      <div class="panel-surface mx-auto w-full max-w-md p-7 sm:p-9">
        <div class="mb-8">
          <p class="text-xs font-black uppercase tracking-[0.28em] text-coffee-400">AMIKOSPACE</p>
          <h2 class="mt-3 text-3xl font-light tracking-wide text-black">Registrasi Pelanggan</h2>
          <p class="mt-2 text-sm leading-6 text-coffee-600">Isi data singkat untuk membuat akun pelanggan.</p>
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

          <div>
            <label for="name" class="mb-2 block text-sm font-black text-coffee-900">Nama</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required class="form-field" placeholder="Nama lengkap">
          </div>

          <div>
            <label for="email" class="mb-2 block text-sm font-black text-coffee-900">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required class="form-field" placeholder="nama@email.com">
          </div>

          <div>
            <label for="phone_number" class="mb-2 block text-sm font-black text-coffee-900">Nomor WhatsApp</label>
            <input id="phone_number" name="phone_number" type="tel" value="{{ old('phone_number') }}" autocomplete="tel" class="form-field" placeholder="08xxxxxxxxxx">
          </div>

          <div class="grid gap-5 sm:grid-cols-2">
            <div>
              <label for="password" class="mb-2 block text-sm font-black text-coffee-900">Password</label>
              <input id="password" name="password" type="password" autocomplete="new-password" required class="form-field" placeholder="Minimal 8 karakter">
            </div>
            <div>
              <label for="password_confirmation" class="mb-2 block text-sm font-black text-coffee-900">Konfirmasi</label>
              <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="form-field" placeholder="Ulangi password">
            </div>
          </div>

          <button type="submit" class="pill-button-dark w-full">Daftar</button>
        </form>

        <p class="mt-6 text-center text-sm text-coffee-600">
          Sudah punya akun?
          <a href="{{ route('login') }}" class="font-black text-black underline-offset-4 hover:underline">Masuk di sini</a>
        </p>
      </div>
    </div>
  </section>
</main>
@endsection
