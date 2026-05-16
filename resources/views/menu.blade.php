@extends('layouts.cafe')

@section('title', 'Menu - AMIKOSPACE Coffee & Tea')
@section('description', 'Menu page untuk memilih menu pre-order reservasi cafe.')
@section('active_page', 'menu')

@section('content')
@php
    $menuItems = [
        [
            'id' => 'signature-latte',
            'name' => 'Signature Latte',
            'category' => 'drink',
            'price' => 38000,
            'description' => 'Espresso lembut dengan susu creamy dan aroma caramel tipis.',
            'badge' => 'Best Seller',
        ],
        [
            'id' => 'matcha-cloud',
            'name' => 'Matcha Cloud',
            'category' => 'drink',
            'price' => 42000,
            'description' => 'Matcha premium, susu segar, dan foam vanilla ringan.',
            'badge' => 'Favorite',
        ],
        [
            'id' => 'black-tea-lemon',
            'name' => 'Black Tea Lemon',
            'category' => 'drink',
            'price' => 30000,
            'description' => 'Teh hitam dingin dengan lemon segar dan aftertaste clean.',
            'badge' => 'Fresh',
        ],
        [
            'id' => 'espresso-tonic',
            'name' => 'Espresso Tonic',
            'category' => 'drink',
            'price' => 40000,
            'description' => 'Espresso single origin dipadukan tonic sparkling.',
            'badge' => 'Sparkling',
        ],
        [
            'id' => 'butter-croissant',
            'name' => 'Butter Croissant',
            'category' => 'pastry',
            'price' => 32000,
            'description' => 'Croissant flaky dengan aroma butter yang rich.',
            'badge' => 'House Baked',
        ],
        [
            'id' => 'almond-pain',
            'name' => 'Almond Pain',
            'category' => 'pastry',
            'price' => 36000,
            'description' => 'Pastry almond dengan filling lembut dan toasted almond.',
            'badge' => 'Sweet',
        ],
        [
            'id' => 'cinnamon-roll',
            'name' => 'Cinnamon Roll',
            'category' => 'pastry',
            'price' => 34000,
            'description' => 'Roll kayu manis hangat dengan glaze tipis.',
            'badge' => 'Warm',
        ],
        [
            'id' => 'banana-bread',
            'name' => 'Banana Bread',
            'category' => 'pastry',
            'price' => 28000,
            'description' => 'Banana bread moist dengan hint dark chocolate.',
            'badge' => 'Classic',
        ],
    ];
@endphp
<main class="min-h-screen pt-28">
  <section x-data='menuCatalog(@js($menuItems))' class="mx-auto max-w-7xl px-5 py-12 lg:px-8">
    <div class="flex flex-col justify-between gap-6 md:flex-row md:items-end">
      <div>
        <p class="text-sm font-black uppercase tracking-[0.28em] text-coffee-400">Menu Page</p>
        <h1 class="mt-4 text-4xl font-light tracking-wide text-black">Pilih menu untuk pre-order.</h1>
        <p class="mt-4 max-w-2xl leading-7 text-coffee-600">
          Tambahkan item ke cart. Kamu tetap bisa reservasi tanpa pre-order, tetapi pre-order membantu cafe menyiapkan pesanan lebih cepat.
        </p>
      </div>

      <div class="flex gap-2 rounded-full bg-white p-2">
        <button
          type="button"
          class="rounded-full px-5 py-2 text-sm font-bold transition"
          :class="filterButtonClass('all')"
          @click="setFilter('all')"
        >
          All
        </button>
        <button
          type="button"
          class="rounded-full px-5 py-2 text-sm font-bold transition"
          :class="filterButtonClass('drink')"
          @click="setFilter('drink')"
        >
          Drink
        </button>
        <button
          type="button"
          class="rounded-full px-5 py-2 text-sm font-bold transition"
          :class="filterButtonClass('pastry')"
          @click="setFilter('pastry')"
        >
          Pastry
        </button>
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
