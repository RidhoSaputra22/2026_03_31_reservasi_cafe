@extends('admin.layouts.app', ['title' => 'Panel Pembayaran', 'breadcrumbs' => [['label' => 'Pembayaran']]])

@php
    $paymentStatusFilterOptions = collect([['value' => '', 'label' => 'Semua status']])
        ->concat($statusOptions)
        ->all();
    $paymentMethodFilterOptions = collect([['value' => '', 'label' => 'Semua metode']])
        ->concat($methodOptions)
        ->all();
    $paymentTypeFilterOptions = collect([['value' => '', 'label' => 'Semua jenis']])
        ->concat($typeOptions)
        ->all();
    $settlementReservations = collect($payments->items())
        ->map(fn ($payment) => $payment->reservation)
        ->filter()
        ->unique('id')
        ->filter(fn ($reservation) => $midtransConfigured && $reservation->canCreateSettlementPayment())
        ->values();
@endphp

@section('header')
    <x-layouts.page-header title="Panel Pembayaran" description="Validasi DP, metode pembayaran, referensi transaksi, dan status reservasi terkait.">
        <x-slot:actions>
            <x-ui.button type="secondary" size="sm" :isSubmit="false"
                onclick="document.getElementById('export-payments-pdf-modal').showModal()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 16V4m0 12-4-4m4 4 4-4M4 20h16" />
                </svg>
                Export PDF
            </x-ui.button>
            <x-ui.button :href="route('admin.reservations.index')" type="ghost" size="sm" :isSubmit="false">Buka Reservasi</x-ui.button>
        </x-slot:actions>
    </x-layouts.page-header>
@endsection

@section('content')
    <div class="space-y-6">
        <x-ui.card title="Antrian Verifikasi Pembayaran">
            <div class="grid gap-4 lg:grid-cols-2">
                @forelse ($payments->getCollection()->take(4) as $payment)
                    @php
                        $reservation = $payment->reservation;
                        $canCreateSettlement = $midtransConfigured
                            && $reservation?->canCreateSettlementPayment();
                    @endphp
                    <form method="POST" action="{{ route('admin.payments.status', $payment) }}" class="rounded-box border border-base-200 bg-base-100 p-4">
                        @csrf
                        @method('PATCH')
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold">{{ $payment->payment_code }}</p>
                                <p class="text-sm text-base-content/60">{{ $payment->reservation?->reservation_code ?? '-' }} · {{ $payment->method->label() }} · Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
                                @if ($payment->type === \App\Enums\PaymentType::FullPayment && $payment->parentPayment)
                                    <p class="mt-1 text-xs text-base-content/60">
                                        Terkait DP {{ $payment->parentPayment->payment_code }} -> SISA
                                    </p>
                                @elseif ($canCreateSettlement)
                                    <p class="mt-1 text-xs text-base-content/60">
                                        DP sudah berhasil. Pembayaran sisa bisa dibuat lewat Midtrans.
                                    </p>
                                @endif
                            </div>
                            <x-ui.badge type="warning" size="sm">{{ $payment->status->label() }}</x-ui.badge>
                        </div>
                        <div class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto]">
                            <x-ui.select name="status" :options="$statusOptions" :selected="$payment->status->value" size="sm" placeholder="Pilih status" />
                            <x-ui.button type="primary" size="sm">Update</x-ui.button>
                        </div>
                        <x-ui.input name="notes" size="sm" placeholder="Catatan verifikasi" class="mt-3" />
                    </form>
                    @if ($canCreateSettlement)
                        <form method="POST" action="{{ route('admin.payments.settlement', $reservation) }}" class="-mt-1 rounded-box border border-dashed border-primary/20 bg-primary/5 p-4">
                            @csrf
                            <input type="hidden" name="method" value="{{ \App\Enums\PaymentMethod::Qris->value }}">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-primary">Buka Pembayaran Sisa</p>
                                    <p class="text-xs text-base-content/70">
                                        Buat transaksi Midtrans pelunasan sebesar
                                        Rp {{ number_format((float) $reservation->remainingAmount(), 0, ',', '.') }}.
                                    </p>
                                </div>
                                <x-ui.button type="primary" size="sm">Buat Sisa Midtrans</x-ui.button>
                            </div>
                        </form>
                    @endif
                @empty
                    <div class="rounded-box border border-dashed border-base-300 p-6 text-center text-base-content/60 lg:col-span-2">
                        Belum ada pembayaran pada filter ini.
                    </div>
                @endforelse
            </div>
        </x-ui.card>

        @if ($settlementReservations->isNotEmpty())
            <x-ui.card title="Buka Pembayaran Sisa">
                <div class="grid gap-4 lg:grid-cols-2">
                    @foreach ($settlementReservations as $reservation)
                        <form method="POST" action="{{ route('admin.payments.settlement', $reservation) }}" class="rounded-box border border-dashed border-primary/20 bg-primary/5 p-4">
                            @csrf
                            <input type="hidden" name="method" value="{{ \App\Enums\PaymentMethod::Qris->value }}">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-semibold text-primary">{{ $reservation->reservation_code }}</p>
                                    <p class="text-sm text-base-content/70">
                                        DP terbayar Rp {{ number_format((float) $reservation->totalPaidAmount(), 0, ',', '.') }} ·
                                        Sisa Rp {{ number_format((float) $reservation->remainingAmount(), 0, ',', '.') }}
                                    </p>
                                </div>
                                <x-ui.button type="primary" size="sm">Buat Sisa Midtrans</x-ui.button>
                            </div>
                        </form>
                    @endforeach
                </div>
            </x-ui.card>
        @endif

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

        <x-reports.export-pdf-modal
            id="export-payments-pdf-modal"
            title="Export Historis Pembayaran"
            description="Pilih filter transaksi yang ingin dimasukkan ke file PDF. Sangat cocok untuk arsip verifikasi, pembukuan, atau laporan bulanan."
            :action="route('admin.reports.payments.pdf')"
        >
            <x-ui.input name="search" label="Kata Kunci" placeholder="Kode pembayaran, kode reservasi, nama pelanggan"
                :value="request('search')" />
            <x-ui.select name="date_field" label="Basis Tanggal" :options="$dateFieldOptions"
                :selected="request('date_field', 'paid_at')" />
            <x-ui.input name="date_from" type="date" label="Dari Tanggal" :value="request('date_from')" />
            <x-ui.input name="date_until" type="date" label="Sampai Tanggal" :value="request('date_until')" />
            <x-ui.select name="status" label="Status Pembayaran" :options="$paymentStatusFilterOptions"
                :selected="request('status')" :placeholder="false" />
            <x-ui.select name="method" label="Metode Pembayaran" :options="$paymentMethodFilterOptions"
                :selected="request('method')" :placeholder="false" />
            <x-ui.select name="type" label="Jenis Pembayaran" :options="$paymentTypeFilterOptions"
                :selected="request('type')" :placeholder="false" />
            <x-ui.input name="min_amount" type="number" min="0" label="Nominal Minimum"
                placeholder="Contoh: 50000" :value="request('min_amount')" />
        </x-reports.export-pdf-modal>
    </div>
@endsection
