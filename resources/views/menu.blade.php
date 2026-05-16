@extends('layouts.cafe')

@section('title', 'Menu - ' . config('cafe.name'))
@section('description', 'Menu page untuk memilih menu pre-order reservasi cafe.')
@section('active_page', 'menu')

@section('content')
@php
    $menuItems = config('cafe.menu_items', []);
@endphp
<main class="min-h-screen pt-28">
  <section class="mx-auto max-w-7xl px-5 py-12 lg:px-8">
    <div class="flex flex-col justify-between gap-6 md:flex-row md:items-end">
      <div>
        <p class="text-sm font-black uppercase tracking-[0.28em] text-coffee-400">Menu Page</p>
        <h1 class="mt-4 text-4xl font-light tracking-wide text-coffee-800">Pilih menu untuk pre-order.</h1>
        <p class="mt-4 max-w-2xl leading-7 text-coffee-600">
          Tambahkan item ke cart. Kamu tetap bisa reservasi tanpa pre-order, tetapi pre-order membantu cafe menyiapkan pesanan lebih cepat.
        </p>
      </div>
      <div class="flex gap-2 rounded-full bg-white p-2 shadow-soft">
        <button class="menu-filter rounded-full bg-coffee-800 px-5 py-2 text-sm font-bold text-white" data-filter="all">All</button>
        <button class="menu-filter rounded-full px-5 py-2 text-sm font-bold text-coffee-700 hover:bg-coffee-100" data-filter="drink">Drink</button>
        <button class="menu-filter rounded-full px-5 py-2 text-sm font-bold text-coffee-700 hover:bg-coffee-100" data-filter="pastry">Pastry</button>
      </div>
    </div>

    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      @foreach ($menuItems as $item)
        @include('components.menu-card', ['item' => $item])
      @endforeach
    </div>
  </section>
</main>
@endsection
