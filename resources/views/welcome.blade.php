@extends('layouts.cafe')

@section('title', 'Landing - AMIKOSPACE Coffee & Tea')
@section('description', 'Landing page aplikasi reservasi cafe AMIKOSPACE Coffee & Tea.')
@section('active_page', 'landing')

@section('content')
<main>
  <section class="hero-bg relative min-h-screen pt-24 text-white">
    <div class="mx-auto flex min-h-[calc(100vh-6rem)] max-w-7xl items-center px-5 py-16 lg:px-8">
      <div class="max-w-3xl">

        <h1 class="text-5xl font-light leading-tight tracking-[0.18em] sm:text-6xl lg:text-7xl">AMIKOSPACE</h1>
        <p class=" text-lg font-semibold tracking-[0.32em] text-white">COFFEE &amp; TEA</p>
        <p class="mt-8 max-w-2xl text-base leading-8 text-coffee-50 sm:text-lg">
          high quality coffee, matcha, tea, and house-baked pastries. Pesan menu favoritmu, pilih jumlah tamu, lalu amankan meja untuk waktu terbaikmu.
        </p>

        <div class="mt-10 flex flex-col gap-3 sm:flex-row">
          <a href="{{ route('cart') }}" class="rounded-full bg-white px-8 py-4 text-center text-sm font-black uppercase tracking-[0.18em] text-black hover:-translate-y-0.5 transition hover:bg-black hover:text-white">
            Reservasi Sekarang
          </a>
          <a href="{{ route('menu') }}" class="rounded-full border border-white/30 bg-white/10 px-8 py-4 text-center text-sm font-black uppercase tracking-[0.18em] text-white backdrop-blur transition hover:-translate-y-0.5 hover:bg-white/20">
            Lihat Menu
          </a>
        </div>

        <div class="mt-14 grid gap-4 sm:grid-cols-3">
          <article class="rounded-xl border border-white/15 bg-white/10 p-5 backdrop-blur">
            <p class="text-sm font-bold uppercase tracking-[0.18em] ">Order Ahead</p>
            <p class="mt-3 text-sm leading-6 text-white/85">Pre-order minuman dan pastry sebelum datang.</p>
          </article>
          <article class="rounded-xl border border-white/15 bg-white/10 p-5 backdrop-blur">
            <p class="text-sm font-bold uppercase tracking-[0.18em] ">Choose Number</p>
            <p class="mt-3 text-sm leading-6 text-white/85">Pilih jumlah tamu dan preferensi tempat duduk.</p>
          </article>
          <article class="rounded-xl border border-white/15 bg-white/10 p-5 backdrop-blur">
            <p class="text-sm font-bold uppercase tracking-[0.18em] ">Reserved</p>
            <p class="mt-3 text-sm leading-6 text-white/85">Dapatkan kode reservasi setelah submit.</p>
          </article>
        </div>
      </div>
    </div>
  </section>

  <section class="mx-auto max-w-7xl px-5 py-20 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[1fr_1.2fr] lg:items-center">
      <div>
        <p class="text-sm font-black uppercase tracking-[0.28em] text-coffee-400">How it works</p>
        <h2 class="mt-4 text-4xl font-light tracking-wide text-black">Alur reservasi yang singkat.</h2>
      </div>
      <div class="grid gap-4 sm:grid-cols-3">
        @php
            $steps = [
                ['number' => '1', 'title' => 'Pilih Menu', 'body' => 'Tambahkan minuman atau pastry ke cart.'],
                ['number' => '2', 'title' => 'Jumlah Tamu', 'body' => 'Isi tanggal, jam, dan jumlah orang.'],
                ['number' => '3', 'title' => 'Reserved', 'body' => 'Reservasi selesai dengan kode booking.'],
            ];
        @endphp
        @foreach ($steps as $step)
          <div class="rounded-3xl bg-white p-6 ">
            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-black font-black text-white">{{ $step['number'] }}</span>
            <h3 class="mt-5 font-black">{{ $step['title'] }}</h3>
            <p class="mt-2 text-sm leading-6 text-coffee-600">{{ $step['body'] }}</p>
          </div>
        @endforeach
      </div>
    </div>
  </section>
</main>
@endsection
