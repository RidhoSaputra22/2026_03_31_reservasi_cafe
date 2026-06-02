@extends('admin.layouts.app', ['title' => 'Panel Pembayaran', 'breadcrumbs' => [['label' => 'Pembayaran']]])

@section('header')
    <x-layouts.page-header title="Panel Pembayaran" description="Validasi DP, metode pembayaran, referensi transaksi, dan status reservasi terkait.">
        <x-slot:actions>
            <x-ui.button :href="route('admin.reservations.index')" type="ghost" size="sm" :isSubmit="false">Buka Reservasi</x-ui.button>
        </x-slot:actions>
    </x-layouts.page-header>
@endsection

@section('content')
    <div class="space-y-6">
        <x-ui.card title="Antrian Verifikasi Pembayaran">
            <div class="grid gap-4 lg:grid-cols-2">
                @forelse ($payments->getCollection()->take(4) as $payment)
                    <form method="POST" action="{{ route('admin.payments.status', $payment) }}" class="rounded-box border border-base-200 bg-base-100 p-4">
                        @csrf
                        @method('PATCH')
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold">{{ $payment->payment_code }}</p>
                                <p class="text-sm text-base-content/60">{{ $payment->reservation?->reservation_code ?? '-' }} · {{ $payment->method->label() }} · Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
                            </div>
                            <x-ui.badge type="warning" size="sm">{{ $payment->status->label() }}</x-ui.badge>
                        </div>
                        <div class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto]">
                            <x-ui.select name="status" :options="$statusOptions" :selected="$payment->status->value" size="sm" placeholder="Pilih status" />
                            <x-ui.button type="primary" size="sm">Update</x-ui.button>
                        </div>
                        <x-ui.input name="notes" size="sm" placeholder="Catatan verifikasi" class="mt-3" />
                    </form>
                @empty
                    <div class="rounded-box border border-dashed border-base-300 p-6 text-center text-base-content/60 lg:col-span-2">
                        Belum ada pembayaran pada filter ini.
                    </div>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card>
            <x-ui.data-table
                title="Daftar Pembayaran"
                :data="$payments"
                :only="['payment_code', 'type', 'amount', 'method', 'status', 'paid_at', 'verified_at']"
                :labels="['payment_code' => 'Kode', 'paid_at' => 'Dibayar', 'verified_at' => 'Diverifikasi']"
                :formats="['type' => 'badge', 'amount' => 'money', 'method' => 'badge', 'status' => 'badge', 'paid_at' => 'datetime', 'verified_at' => 'datetime']"
                :sortable="['payment_code', 'amount', 'method', 'status', 'paid_at', 'verified_at']"
                :selectable="false"
                :delete-route="fn ($row) => route('admin.payments.destroy', $row)"
            >
                <x-slot:filters>
                    <x-ui.select name="status" :options="$statusOptions" :selected="request('status')" placeholder="Semua status" size="sm" class="min-w-48" />
                </x-slot:filters>
            </x-ui.data-table>
        </x-ui.card>
    </div>
@endsection
