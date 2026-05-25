@extends('layouts.cafe')

@section('title', 'Profil Pelanggan - AMIKOSPACE Coffee & Tea')
@section('description', 'Profil pelanggan untuk melihat riwayat pesanan dan pengingat reservasi AMIKOSPACE Coffee & Tea.')
@section('active_page', 'account')

@section('content')
@php
    $nextDateTime = $nextReservation?->reservation_date?->copy()?->setTimeFromTimeString($nextReservation->start_time);
    $nextStatus = $nextReservation?->status?->value;
    $nextStatusClass = match ($nextStatus) {
        'confirmed', 'checked_in' => 'border-green-200 bg-green-50 text-green-700',
        'awaiting_confirmation' => 'border-amber-200 bg-amber-50 text-amber-700',
        'pending_payment' => 'border-orange-200 bg-orange-50 text-orange-700',
        default => 'border-coffee-200 bg-coffee-50 text-coffee-700',
    };
    $statsCards = [
        ['label' => 'Total Reservasi', 'value' => $stats['total']],
        ['label' => 'Reservasi Aktif', 'value' => $stats['upcoming']],
        ['label' => 'Selesai', 'value' => $stats['completed']],
        ['label' => 'Perlu Dicek', 'value' => $stats['needs_action']],
    ];
@endphp

<main class="min-h-screen pt-28">
  <section class="mx-auto max-w-7xl px-5 py-12 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[1fr_22rem] lg:items-start">
      <div>
        <p class="text-sm font-black uppercase tracking-[0.28em] text-coffee-400">Akun Pelanggan</p>
        <h1 class="mt-4 text-4xl font-light tracking-wide text-black sm:text-5xl">Halo, {{ $user->name }}</h1>
        <p class="mt-4 max-w-2xl leading-7 text-coffee-600">
          Pantau reservasi, pembayaran, dan catatan kedatanganmu dalam satu tempat.
        </p>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
          <a href="{{ route('cart') }}" class="pill-button-dark">Reservasi Baru</a>
          <a href="{{ route('menu') }}" class="pill-button-light">Lihat Menu</a>
        </div>
      </div>

      <aside class="panel-surface p-6">
        <p class="text-sm font-black uppercase tracking-[0.2em] text-coffee-400">Data Akun</p>
        <div class="mt-5 grid gap-4 text-sm">
          <div>
            <p class="text-coffee-500">Nama</p>
            <p class="mt-1 font-black text-black">{{ $user->name }}</p>
          </div>
          <div>
            <p class="text-coffee-500">Email</p>
            <p class="mt-1 break-all font-black text-black">{{ $user->email }}</p>
          </div>
          <div>
            <p class="text-coffee-500">WhatsApp</p>
            <p class="mt-1 font-black text-black">{{ $user->phone_number ?: '-' }}</p>
          </div>
          <div>
            <p class="text-coffee-500">Member Sejak</p>
            <p class="mt-1 font-black text-black">{{ $user->created_at?->format('d M Y') }}</p>
          </div>
        </div>
      </aside>
    </div>

    <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      @foreach ($statsCards as $card)
        <article class="rounded-[1.5rem] border border-coffee-100 bg-white p-5">
          <p class="text-sm font-black uppercase tracking-[0.18em] text-coffee-400">{{ $card['label'] }}</p>
          <p class="mt-4 text-4xl font-light text-black">{{ $card['value'] }}</p>
        </article>
      @endforeach
    </div>

    <div class="mt-10 grid gap-8 lg:grid-cols-[0.95fr_1.05fr]">
      <section class="panel-surface p-6 sm:p-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
          <div>
            <p class="text-sm font-black uppercase tracking-[0.2em] text-coffee-400">Pengingat Reservasi</p>
            <h2 class="mt-2 text-2xl font-black text-black">Jadwal terdekat</h2>
          </div>
          @if ($nextReservation)
            <span class="w-fit rounded-full border px-4 py-2 text-xs font-black uppercase tracking-[0.14em] {{ $nextStatusClass }}">
              {{ $nextReservation->status->label() }}
            </span>
          @endif
        </div>

        @if ($nextReservation)
          <div class="mt-8 rounded-[1.5rem] bg-coffee-50 p-5">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-coffee-400">{{ $nextReservation->reservation_code }}</p>
            <h3 class="mt-3 text-3xl font-light text-black">
              @if ($nextDateTime?->isToday())
                Hari ini
              @elseif ($nextDateTime?->isTomorrow())
                Besok
              @else
                {{ $nextReservation->reservation_date?->format('d M Y') }}
              @endif
            </h3>
            <dl class="mt-5 grid gap-3 text-sm text-coffee-700 sm:grid-cols-2">
              <div>
                <dt>Jam</dt>
                <dd class="mt-1 font-black text-coffee-900">{{ substr($nextReservation->start_time, 0, 5) }} - {{ substr($nextReservation->end_time ?: $nextReservation->start_time, 0, 5) }}</dd>
              </div>
              <div>
                <dt>Meja</dt>
                <dd class="mt-1 font-black text-coffee-900">{{ $nextReservation->cafeTable?->name ?? 'Menunggu meja' }}</dd>
              </div>
              <div>
                <dt>Jumlah Tamu</dt>
                <dd class="mt-1 font-black text-coffee-900">{{ $nextReservation->guest_count }} orang</dd>
              </div>
              <div>
                <dt>Tagihan</dt>
                <dd class="mt-1 font-black text-coffee-900">Rp {{ number_format((float) $nextReservation->amount_due, 0, ',', '.') }}</dd>
              </div>
            </dl>
            <p class="mt-5 text-sm leading-6 text-coffee-600">
              Datang 10 menit lebih awal dan tunjukkan kode reservasi kepada staff.
            </p>
          </div>
        @else
          <div class="mt-8 rounded-[1.5rem] border border-dashed border-coffee-200 bg-coffee-50 p-8 text-center">
            <p class="text-xl font-black text-black">Belum ada reservasi aktif</p>
            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-coffee-600">Buat reservasi baru untuk menampilkan pengingat kedatangan di sini.</p>
            <a href="{{ route('cart') }}" class="pill-button-dark mt-6">Mulai Reservasi</a>
          </div>
        @endif
      </section>

      <section class="panel-surface p-6 sm:p-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
          <div>
            <p class="text-sm font-black uppercase tracking-[0.2em] text-coffee-400">Reservasi Aktif</p>
            <h2 class="mt-2 text-2xl font-black text-black">Antrian jadwalmu</h2>
          </div>
          <a href="{{ route('cart') }}" class="pill-button-light">Tambah</a>
        </div>

        <div class="mt-6 grid gap-3">
          @forelse ($upcomingReservations as $reservation)
            @php
                $statusValue = $reservation->status?->value;
                $statusClass = match ($statusValue) {
                    'confirmed', 'checked_in' => 'bg-green-50 text-green-700',
                    'awaiting_confirmation' => 'bg-amber-50 text-amber-700',
                    'pending_payment' => 'bg-orange-50 text-orange-700',
                    default => 'bg-coffee-50 text-coffee-700',
                };
            @endphp
            <article class="grid gap-4 rounded-[1.25rem] border border-coffee-100 p-4 sm:grid-cols-[1fr_auto] sm:items-center">
              <div>
                <p class="text-xs font-black uppercase tracking-[0.16em] text-coffee-400">{{ $reservation->reservation_code }}</p>
                <h3 class="mt-2 font-black text-black">{{ $reservation->reservation_date?->format('d M Y') }} pukul {{ substr($reservation->start_time, 0, 5) }}</h3>
                <p class="mt-1 text-sm text-coffee-600">{{ $reservation->cafeTable?->name ?? 'Meja otomatis' }} - {{ $reservation->guest_count }} tamu</p>
              </div>
              <span class="w-fit rounded-full px-3 py-1.5 text-xs font-black {{ $statusClass }}">{{ $reservation->status->label() }}</span>
            </article>
          @empty
            <div class="rounded-[1.25rem] border border-dashed border-coffee-200 bg-coffee-50 p-6 text-sm leading-6 text-coffee-600">
              Tidak ada jadwal aktif yang menunggu.
            </div>
          @endforelse
        </div>
      </section>
    </div>

    <section data-profile-local-reservation class="mt-8 hidden"></section>

    <section class="panel-surface mt-10 overflow-hidden p-6 sm:p-8">
      <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
          <p class="text-sm font-black uppercase tracking-[0.2em] text-coffee-400">Riwayat Pesanan</p>
          <h2 class="mt-2 text-2xl font-black text-black">Reservasi dan pembayaran</h2>
        </div>
        <a href="{{ route('menu') }}" class="pill-button-light">Pesan Menu</a>
      </div>

      @if ($reservations->isNotEmpty())
        <div class="mt-8 overflow-x-auto rounded-[1.5rem] border border-coffee-100">
          <table class="min-w-full divide-y divide-coffee-100 bg-white text-left text-sm">
            <thead class="bg-coffee-50 text-xs font-black uppercase tracking-[0.16em] text-coffee-500">
              <tr>
                <th class="px-5 py-4">Kode</th>
                <th class="px-5 py-4">Jadwal</th>
                <th class="px-5 py-4">Meja</th>
                <th class="px-5 py-4">Pembayaran</th>
                <th class="px-5 py-4">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-coffee-100">
              @foreach ($reservations as $reservation)
                @php
                    $latestPayment = $reservation->payments->first();
                    $statusValue = $reservation->status?->value;
                    $historyStatusClass = match ($statusValue) {
                        'completed' => 'bg-black text-white',
                        'confirmed', 'checked_in' => 'bg-green-50 text-green-700',
                        'cancelled' => 'bg-red-50 text-red-700',
                        'awaiting_confirmation' => 'bg-amber-50 text-amber-700',
                        'pending_payment' => 'bg-orange-50 text-orange-700',
                        default => 'bg-coffee-50 text-coffee-700',
                    };
                @endphp
                <tr class="align-top">
                  <td class="px-5 py-4">
                    <p class="font-black text-black">{{ $reservation->reservation_code }}</p>
                    <p class="mt-1 text-xs text-coffee-500">{{ $reservation->customer_name }}</p>
                  </td>
                  <td class="px-5 py-4 text-coffee-700">
                    <p class="font-black text-coffee-900">{{ $reservation->reservation_date?->format('d M Y') }}</p>
                    <p class="mt-1">{{ substr($reservation->start_time, 0, 5) }} - {{ substr($reservation->end_time ?: $reservation->start_time, 0, 5) }}</p>
                  </td>
                  <td class="px-5 py-4 text-coffee-700">
                    <p>{{ $reservation->cafeTable?->name ?? '-' }}</p>
                    <p class="mt-1 text-xs">{{ $reservation->guest_count }} tamu</p>
                  </td>
                  <td class="px-5 py-4 text-coffee-700">
                    @if ($latestPayment)
                      <p class="font-black text-coffee-900">Rp {{ number_format((float) $latestPayment->amount, 0, ',', '.') }}</p>
                      <p class="mt-1 text-xs">{{ $latestPayment->method->label() }} - {{ $latestPayment->status->label() }}</p>
                    @else
                      <p class="font-black text-coffee-900">Rp {{ number_format((float) $reservation->amount_due, 0, ',', '.') }}</p>
                      <p class="mt-1 text-xs">Belum ada pembayaran</p>
                    @endif
                  </td>
                  <td class="px-5 py-4">
                    <span class="inline-flex rounded-full px-3 py-1.5 text-xs font-black {{ $historyStatusClass }}">{{ $reservation->status->label() }}</span>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <div class="mt-8 rounded-[1.5rem] border border-dashed border-coffee-200 bg-coffee-50 p-8 text-center">
          <p class="text-xl font-black text-black">Riwayat masih kosong</p>
          <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-coffee-600">Reservasi yang terhubung dengan akunmu akan tampil setelah dibuat.</p>
          <a href="{{ route('cart') }}" class="pill-button-dark mt-6">Reservasi Sekarang</a>
        </div>
      @endif
    </section>
  </section>
</main>
@endsection
