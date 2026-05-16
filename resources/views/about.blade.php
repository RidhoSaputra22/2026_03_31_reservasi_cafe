@extends('layouts.cafe')

@section('title', 'About - AMIKOSPACE Coffee & Tea')
@section('description', 'About page AMIKOSPACE Coffee & Tea.')
@section('active_page', 'about')

@section('content')
<main class="min-h-screen pt-28">
  <section class="mx-auto max-w-7xl px-5 py-12 lg:px-8">
    <div class="grid gap-10 lg:grid-cols-[1.1fr_.9fr] lg:items-center">
      <div>
        <p class="text-sm font-black uppercase tracking-[0.28em] text-coffee-400">About Page</p>
        <h1 class="mt-4 text-4xl font-light tracking-wide text-black sm:text-5xl">Tempat singgah untuk jeda yang tenang.</h1>
        <p class="mt-6 leading-8 text-coffee-600">
          AMIKOSPACE Coffee &amp; Tea adalah cafe bernuansa hangat yang fokus pada kopi berkualitas, matcha, teh pilihan, dan pastry yang dipanggang setiap hari. Template ini disiapkan sebagai user page aplikasi reservasi cafe dengan alur sederhana dari landing, menu, cart, choose number, sampai reserved.
        </p>
        <div class="mt-8 grid gap-4 sm:grid-cols-3">
          <div class="rounded-3xl bg-white p-6 ">
            <p class="text-3xl font-light text-black">08+</p>
            <p class="mt-2 text-sm font-bold text-coffee-600">Signature menu</p>
          </div>
          <div class="rounded-3xl bg-white p-6 ">
            <p class="text-3xl font-light text-black">40</p>
            <p class="mt-2 text-sm font-bold text-coffee-600">Seat capacity</p>
          </div>
          <div class="rounded-3xl bg-white p-6 ">
            <p class="text-3xl font-light text-black">3</p>
            <p class="mt-2 text-sm font-bold text-coffee-600">Reservasi steps</p>
          </div>
        </div>
      </div>
      <div class="about-bg min-h-[520px] rounded-[2.5rem] "></div>
    </div>
  </section>

  <section class="mx-auto max-w-7xl px-5 pb-20 lg:px-8">
    <div class="grid gap-6 md:grid-cols-3">
      <article class="rounded-[2rem] bg-white p-7 ">
        <p class="font-black text-coffee-900">Coffee</p>
        <p class="mt-3 text-sm leading-6 text-coffee-600">Espresso based, manual brew, dan seasonal coffee.</p>
      </article>
      <article class="rounded-[2rem] bg-white p-7 ">
        <p class="font-black text-coffee-900">Tea &amp; Matcha</p>
        <p class="mt-3 text-sm leading-6 text-coffee-600">Tea blend, matcha latte, dan minuman segar non-coffee.</p>
      </article>
      <article class="rounded-[2rem] bg-white p-7 ">
        <p class="font-black text-coffee-900">Pastry</p>
        <p class="mt-3 text-sm leading-6 text-coffee-600">Croissant, roll, cake, dan house-baked pastry harian.</p>
      </article>
    </div>
  </section>
</main>
@endsection
