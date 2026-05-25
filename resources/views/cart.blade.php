@extends('layouts.cafe')

@section('title', 'Cart dan Reservasi - AMIKOSPACE Coffee & Tea')
@section('description', 'Cart page dengan alur Cart, Choose Number, dan Reserved.')
@section('active_page', 'cart')

@section('content')
<main class="min-h-screen pt-28">
  <section class="mx-auto max-w-6xl px-5 py-12 lg:px-8">
    <div class="text-center">
      <p class="text-sm font-black uppercase tracking-[0.28em] text-coffee-400">Cart Page</p>
      <h1 class="mt-4 text-4xl font-light tracking-wide text-black">Cart - Choose Number - Reserved</h1>
      <p class="mx-auto mt-4 max-w-2xl leading-7 text-coffee-600">Review pesanan, pilih jumlah tamu dan jadwal, lalu selesaikan reservasi.</p>
    </div>

    <div class="mx-auto mt-10 grid max-w-3xl grid-cols-3 gap-3 rounded-full bg-white p-2 ">
      <button type="button" class="reservation-step active rounded-full bg-black px-3 py-3 text-sm font-black text-white" data-step="cart">1. Cart</button>
      <button type="button" class="reservation-step rounded-full px-3 py-3 text-sm font-black text-coffee-600" data-step="guest">2. Choose Number</button>
      <button type="button" class="reservation-step rounded-full px-3 py-3 text-sm font-black text-coffee-600" data-step="reserved">3. Reserved</button>
    </div>

    <div class="panel-surface mt-10 p-6 sm:p-8">
      <section id="cartPanel" class="reservation-panel" data-panel="cart">
        <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
          <div>
            <p class="text-sm font-black uppercase tracking-[0.2em] text-coffee-400">Step 1</p>
            <h2 class="mt-2 text-2xl font-black text-coffee-900">Cart Pesanan</h2>
          </div>
          <a href="{{ route('menu') }}" class="pill-button-light">Tambah Menu</a>
        </div>

        <div id="cartItems" class="mt-8 grid gap-4"></div>

        <div class="mt-8 rounded-3xl bg-coffee-50 p-5">
          <div class="flex items-center justify-between gap-4">
            <p class="font-black text-black">Subtotal Pre-order</p>
            <p id="cartTotal" class="text-xl font-black text-coffee-900">Rp 0</p>
          </div>
          <p class="mt-2 text-sm leading-6 text-coffee-600">Pembayaran belum diproses di template ini. Total hanya untuk simulasi pre-order.</p>
        </div>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-end">
          <button type="button" id="clearCartBtn" class="pill-button-light">Kosongkan Cart</button>
          <button type="button" id="toGuestStep" class="pill-button-dark">Lanjut Pilih Jumlah</button>
        </div>
      </section>

      <section id="guestPanel" class="reservation-panel hidden" data-panel="guest">
        <p class="text-sm font-black uppercase tracking-[0.2em] text-coffee-400">Step 2</p>
        <h2 class="mt-2 text-2xl font-black text-coffee-900">Choose Number dan Jadwal</h2>
        <p class="mt-2 text-sm leading-6 text-coffee-600">Isi data reservasi. Field bertanda * wajib diisi.</p>

        <form id="reservationForm" class="mt-8 grid gap-5 md:grid-cols-2">
          <label class="grid gap-2">
            <span class="text-sm font-black text-coffee-700">Nama Pemesan *</span>
            <input name="guestName" value="{{ auth()->user()?->name }}" required class="form-field" placeholder="Nama kamu">
          </label>
          <label class="grid gap-2">
            <span class="text-sm font-black text-coffee-700">Nomor WhatsApp *</span>
            <input name="phone" value="{{ auth()->user()?->phone_number }}" required class="form-field" placeholder="08xxxxxxxxxx">
          </label>
          <label class="grid gap-2">
            <span class="text-sm font-black text-coffee-700">Jumlah Tamu *</span>
            <input name="guests" required type="number" min="1" max="20" value="2" class="form-field">
          </label>
          <label class="grid gap-2">
            <span class="text-sm font-black text-coffee-700">Area Duduk *</span>
            <select name="seat" required class="form-field">
              <option value="Indoor">Indoor</option>
              <option value="Outdoor">Outdoor</option>
              <option value="Window Seat">Window Seat</option>
              <option value="Bar Area">Bar Area</option>
            </select>
          </label>
          <label class="grid gap-2">
            <span class="text-sm font-black text-coffee-700">Tanggal *</span>
            <input name="date" required type="date" class="form-field">
          </label>
          <label class="grid gap-2">
            <span class="text-sm font-black text-coffee-700">Jam *</span>
            <input name="time" required type="time" class="form-field">
          </label>
          <label class="grid gap-2 md:col-span-2">
            <span class="text-sm font-black text-coffee-700">Catatan</span>
            <textarea name="note" rows="4" class="form-field" placeholder="Contoh: minta dekat stop kontak, kursi bayi, atau datang untuk ulang tahun."></textarea>
          </label>

          <div class="flex flex-col gap-3 md:col-span-2 sm:flex-row sm:justify-end">
            <button type="button" id="backToCartStep" class="pill-button-light">Kembali ke Cart</button>
            <button type="submit" class="pill-button-dark">Reserved</button>
          </div>
        </form>
      </section>

      <section id="reservedPanel" class="reservation-panel hidden" data-panel="reserved">
        <div class="rounded-[2rem] bg-coffee-50 p-6 text-center">
          <p class="text-sm font-black uppercase tracking-[0.2em] text-coffee-400">Step 3</p>
          <h2 class="mt-2 text-3xl font-light tracking-wide text-coffee-900">Reservasi Berhasil</h2>
          <p class="mx-auto mt-3 max-w-2xl text-sm leading-6 text-coffee-600">Simpan kode reservasi dan tunjukkan kepada staff saat datang.</p>
          <p id="reservationCode" class="mx-auto mt-6 inline-flex rounded-full bg-white px-6 py-3 text-xl font-black tracking-[0.16em] text-black ">INT-000000</p>
        </div>

        <div id="reservationSummary" class="mt-8 grid gap-4"></div>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-end">
          <a href="{{ route('menu') }}" class="pill-button-light">Pesan Lagi</a>
          @auth
            <a href="{{ route('customer.profile') }}" class="pill-button-dark">Lihat Profil</a>
          @else
            <a href="{{ route('landing') }}" class="pill-button-dark">Kembali ke Landing</a>
          @endauth
        </div>
      </section>
    </div>
  </section>
</main>
@endsection
