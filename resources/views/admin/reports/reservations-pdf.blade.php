@extends('admin.reports.layouts.pdf')

@section('report-content')
    <h2 class="section-title">Data Historis Reservasi</h2>

    @if ($reservations->isEmpty())
        <div class="empty-state">
            Tidak ada data reservasi yang cocok dengan filter export ini.
        </div>
    @else
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 12%;">Kode</th>
                    <th style="width: 10%;">Tanggal</th>
                    <th style="width: 8%;">Jam</th>
                    <th style="width: 16%;">Pelanggan</th>
                    <th style="width: 13%;">Kontak</th>
                    <th style="width: 16%;">Meja / Slot</th>
                    <th style="width: 7%;">Tamu</th>
                    <th style="width: 8%;">Status</th>
                    <th style="width: 10%;">Sisa Tagihan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reservations as $reservation)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $reservation->reservation_code }}</strong>
                            @if ($reservation->package_name)
                                <div class="muted">{{ $reservation->package_name }}</div>
                            @endif
                        </td>
                        <td>{{ $reservation->reservation_date?->translatedFormat('d M Y') ?? '-' }}</td>
                        <td>
                            {{ substr((string) $reservation->start_time, 0, 5) }}
                            @if ($reservation->end_time)
                                <div class="muted">{{ substr((string) $reservation->end_time, 0, 5) }}</div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $reservation->customer_name }}</strong>
                            @if ($reservation->notes)
                                <div class="muted">{{ $reservation->notes }}</div>
                            @endif
                        </td>
                        <td>{{ $reservation->customer_phone ?: '-' }}</td>
                        <td>
                            <strong>{{ $reservation->cafeTable?->name ?? 'Belum ditentukan' }}</strong>
                            <div class="muted">
                                {{ $reservation->cafeTable?->code ?? '-' }}
                                @if ($reservation->reservationSlot?->name)
                                    · {{ $reservation->reservationSlot->name }}
                                @endif
                            </div>
                        </td>
                        <td>{{ number_format((int) $reservation->guest_count, 0, ',', '.') }}</td>
                        <td>{{ $reservation->status->label() }}</td>
                        <td>Rp {{ number_format((float) $reservation->amount_due, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
