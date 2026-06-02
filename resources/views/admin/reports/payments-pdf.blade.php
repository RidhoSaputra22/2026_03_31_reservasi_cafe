@extends('admin.reports.layouts.pdf')

@section('report-content')
    <h2 class="section-title">Data Historis Pembayaran</h2>

    @if ($payments->isEmpty())
        <div class="empty-state">
            Tidak ada data pembayaran yang cocok dengan filter export ini.
        </div>
    @else
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 12%;">Kode</th>
                    <th style="width: 12%;">Reservasi</th>
                    <th style="width: 15%;">Pelanggan</th>
                    <th style="width: 8%;">Jenis</th>
                    <th style="width: 10%;">Metode</th>
                    <th style="width: 9%;">Status</th>
                    <th style="width: 10%;">Nominal</th>
                    <th style="width: 10%;">Dibayar</th>
                    <th style="width: 10%;">Diverifikasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $payment)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $payment->payment_code }}</strong>
                            @if ($payment->transaction_reference)
                                <div class="muted">{{ $payment->transaction_reference }}</div>
                            @endif
                        </td>
                        <td>{{ $payment->reservation?->reservation_code ?? '-' }}</td>
                        <td>
                            <strong>{{ $payment->reservation?->customer_name ?? '-' }}</strong>
                            @if ($payment->reservation?->customer_phone)
                                <div class="muted">{{ $payment->reservation->customer_phone }}</div>
                            @endif
                        </td>
                        <td>{{ $payment->type->label() }}</td>
                        <td>{{ $payment->method->label() }}</td>
                        <td>{{ $payment->status->label() }}</td>
                        <td>Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                        <td>{{ $payment->paid_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                        <td>{{ $payment->verified_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
