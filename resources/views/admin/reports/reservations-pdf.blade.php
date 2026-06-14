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
                    <th >No</th>
                    <th >Kode</th>
                    <th >Tanggal</th>
                    {{-- <th >Jam</th> --}}
                    <th >Pelanggan</th>
                    <th >Kontak</th>
                    <th >Meja / Slot</th>
                    <th >Tamu</th>
                    <th >Status</th>
                    <th >Sisa Tagihan</th>
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
                        <td>{{ $reservation->reservation_date?->translatedFormat('d M Y') ?? '-' }}
                            {{ substr((string) $reservation->start_time, 0, 5) }}
                            @if ($reservation->end_time)
                            {{ substr((string) $reservation->end_time, 0, 5) }}
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
