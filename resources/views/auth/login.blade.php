@extends('layouts.cafe')

@section('title', 'Login Pelanggan - AMIKOSPACE Coffee & Tea')
@section('description', 'Portal login pelanggan AMIKOSPACE Coffee & Tea.')
@section('active_page', 'login')

@section('content')
<main class="min-h-screen pt-28">
  <section class="relative overflow-hidden px-5 py-12 lg:px-8">
    <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_18%_18%,rgba(111,75,50,0.14),transparent_28rem),radial-gradient(circle_at_82%_0%,rgba(22,16,13,0.08),transparent_24rem)]"></div>

    <div class="mx-auto grid max-w-6xl gap-8 lg:grid-cols-[0.85fr_1fr] lg:items-center">
      <div class="hidden lg:block">
        <p class="text-sm font-black uppercase tracking-[0.28em] text-coffee-400">Member Area</p>
        <h1 class="mt-4 max-w-xl text-5xl font-light leading-tight tracking-wide text-black">Masuk untuk lanjut reservasi.</h1>
        <p class="mt-5 max-w-lg leading-7 text-coffee-600">
          Simpan data kontakmu agar proses reservasi berikutnya lebih singkat dan rapi.
        </p>
        <a href="{{ route('admin.login') }}" class="mt-8 inline-flex text-sm font-black uppercase tracking-[0.16em] text-coffee-500 underline-offset-4 hover:text-black hover:underline">
          Portal admin
        </a>
      </div>

      <div class="panel-surface mx-auto w-full max-w-md p-7 sm:p-9">
        <div class="mb-8">
          <p class="text-xs font-black uppercase tracking-[0.28em] text-coffee-400">AMIKOSPACE</p>
          <h2 class="mt-3 text-3xl font-light tracking-wide text-black">Login Pelanggan</h2>
          <p class="mt-2 text-sm leading-6 text-coffee-600">Gunakan email pelanggan untuk masuk.</p>
        </div>

        @if ($errors->any())
          <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-black text-red-900">Login belum berhasil</p>
            <ul class="mt-2 space-y-1">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
          @csrf

          <div>
            <label for="email" class="mb-2 block text-sm font-black text-coffee-900">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required class="form-field" placeholder="nama@email.com">
          </div>

          <div>
            <label for="password" class="mb-2 block text-sm font-black text-coffee-900">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required class="form-field" placeholder="Password akun">
          </div>

          <div class="flex items-center justify-between gap-4">
            <label class="flex items-center gap-3 text-sm font-semibold text-coffee-600">
              <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="h-4 w-4 rounded border-coffee-300 text-coffee-900 focus:ring-coffee-200">
              Ingat saya
            </label>
          </div>

          <button type="submit" class="pill-button-dark w-full">Masuk</button>
        </form>

        <p class="mt-6 text-center text-sm text-coffee-600">
          Belum punya akun?
          <a href="{{ route('register') }}" class="font-black text-black underline-offset-4 hover:underline">Daftar pelanggan</a>
        </p>
      </div>
    </div>
  </section>
</main>
@endsection
