<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExportPaymentReportRequest;
use App\Http\Requests\Admin\ExportReservationReportRequest;
use App\Models\CafeProfile;
use App\Models\CafeTable;
use App\Models\Payment;
use App\Models\Reservation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class AdminReportExportController extends Controller
{
    public function reservationsPdf(ExportReservationReportRequest $request): Response
    {
        $filters = $request->validated();

        $query = Reservation::query()->with(['cafeTable', 'reservationSlot', 'payments']);

        $this->applyReservationFilters($query, $filters);

        $reservations = $query
            ->orderBy('reservation_date')
            ->orderBy('start_time')
            ->orderBy('reservation_code')
            ->get();

        $selectedTable = isset($filters['cafe_table_id'])
            ? CafeTable::query()->find($filters['cafe_table_id'])
            : null;

        $summaryStats = [
            ['label' => 'Total Reservasi', 'value' => number_format($reservations->count(), 0, ',', '.')],
            ['label' => 'Total Tamu', 'value' => number_format((int) $reservations->sum('guest_count'), 0, ',', '.')],
            ['label' => 'Total Sisa Tagihan', 'value' => $this->rupiah($reservations->sum('amount_due'))],
            [
                'label' => 'Reservasi Selesai',
                'value' => number_format($reservations->filter(
                    fn (Reservation $reservation): bool => $reservation->status === ReservationStatus::Completed,
                )->count(), 0, ',', '.'),
            ],
        ];

        $pdf = Pdf::loadView('admin.reports.reservations-pdf', [
            'title' => 'Laporan Historis Reservasi',
            'subtitle' => 'Rekap riwayat reservasi berdasarkan filter yang dipilih dari panel admin.',
            'profile' => $this->profile(),
            'exportedBy' => $request->user()?->name ?? 'Admin',
            'exportedAt' => now(),
            'filters' => [
                ['label' => 'Dari', 'value' => $this->formatDate($filters['date_from'] ?? null, 'Semua tanggal')],
                ['label' => 'Sampai', 'value' => $this->formatDate($filters['date_until'] ?? null, 'Semua tanggal')],
                ['label' => 'Status', 'value' => isset($filters['status']) ? ReservationStatus::from($filters['status'])->label() : 'Semua status'],
                ['label' => 'Meja / Area', 'value' => $selectedTable ? trim($selectedTable->code.' · '.$selectedTable->name) : 'Semua meja'],
                ['label' => 'Minimum Tamu', 'value' => isset($filters['min_guest_count']) ? $filters['min_guest_count'].' tamu' : 'Tanpa batas minimum'],
                ['label' => 'Kata Kunci', 'value' => $filters['search'] ?? 'Tidak ada'],
            ],
            'summaryStats' => $summaryStats,
            'reservations' => $reservations,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-reservasi-'.now()->format('Ymd-His').'.pdf');
    }

    public function paymentsPdf(ExportPaymentReportRequest $request): Response
    {
        $filters = $request->validated();

        $query = Payment::query()->with(['reservation', 'verifiedBy']);

        $this->applyPaymentFilters($query, $filters);

        $payments = $query
            ->orderByDesc($filters['date_field'] ?? 'paid_at')
            ->orderByDesc('created_at')
            ->get();

        $summaryStats = [
            ['label' => 'Total Transaksi', 'value' => number_format($payments->count(), 0, ',', '.')],
            ['label' => 'Total Nominal', 'value' => $this->rupiah($payments->sum('amount'))],
            [
                'label' => 'Pembayaran Berhasil',
                'value' => number_format($payments->filter(
                    fn (Payment $payment): bool => $payment->status === PaymentStatus::Paid,
                )->count(), 0, ',', '.'),
            ],
            [
                'label' => 'Pelunasan',
                'value' => number_format($payments->filter(
                    fn (Payment $payment): bool => $payment->type === PaymentType::FullPayment,
                )->count(), 0, ',', '.'),
            ],
        ];

        $pdf = Pdf::loadView('admin.reports.payments-pdf', [
            'title' => 'Laporan Historis Pembayaran',
            'subtitle' => 'Ringkasan transaksi pembayaran reservasi dengan filter export dari panel admin.',
            'profile' => $this->profile(),
            'exportedBy' => $request->user()?->name ?? 'Admin',
            'exportedAt' => now(),
            'filters' => [
                ['label' => 'Basis Tanggal', 'value' => $this->paymentDateFieldLabels()[$filters['date_field'] ?? 'paid_at']],
                ['label' => 'Dari', 'value' => $this->formatDate($filters['date_from'] ?? null, 'Semua tanggal')],
                ['label' => 'Sampai', 'value' => $this->formatDate($filters['date_until'] ?? null, 'Semua tanggal')],
                ['label' => 'Status', 'value' => isset($filters['status']) ? PaymentStatus::from($filters['status'])->label() : 'Semua status'],
                ['label' => 'Metode', 'value' => isset($filters['method']) ? PaymentMethod::from($filters['method'])->label() : 'Semua metode'],
                ['label' => 'Jenis', 'value' => isset($filters['type']) ? PaymentType::from($filters['type'])->label() : 'Semua jenis'],
                ['label' => 'Minimum Nominal', 'value' => isset($filters['min_amount']) ? $this->rupiah($filters['min_amount']) : 'Tanpa batas minimum'],
                ['label' => 'Kata Kunci', 'value' => $filters['search'] ?? 'Tidak ada'],
            ],
            'summaryStats' => $summaryStats,
            'payments' => $payments,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-pembayaran-'.now()->format('Ymd-His').'.pdf');
    }

    /**
     * @param array<string, mixed> $filters
     */
    protected function applyReservationFilters(Builder $query, array $filters): void
    {
        $keyword = trim((string) ($filters['search'] ?? ''));

        if ($keyword !== '') {
            $query->where(function (Builder $builder) use ($keyword): void {
                $builder
                    ->where('reservation_code', 'like', "%{$keyword}%")
                    ->orWhere('customer_name', 'like', "%{$keyword}%")
                    ->orWhere('customer_phone', 'like', "%{$keyword}%")
                    ->orWhere('package_name', 'like', "%{$keyword}%")
                    ->orWhere('notes', 'like', "%{$keyword}%")
                    ->orWhereHas('cafeTable', function (Builder $tableQuery) use ($keyword): void {
                        $tableQuery
                            ->where('code', 'like', "%{$keyword}%")
                            ->orWhere('name', 'like', "%{$keyword}%")
                            ->orWhere('location', 'like', "%{$keyword}%");
                    });
            });
        }

        $query
            ->when(isset($filters['status']), fn (Builder $builder) => $builder->where('status', $filters['status']))
            ->when(isset($filters['date_from']), fn (Builder $builder) => $builder->whereDate('reservation_date', '>=', $filters['date_from']))
            ->when(isset($filters['date_until']), fn (Builder $builder) => $builder->whereDate('reservation_date', '<=', $filters['date_until']))
            ->when(isset($filters['cafe_table_id']), fn (Builder $builder) => $builder->where('cafe_table_id', $filters['cafe_table_id']))
            ->when(isset($filters['min_guest_count']), fn (Builder $builder) => $builder->where('guest_count', '>=', $filters['min_guest_count']));
    }

    /**
     * @param array<string, mixed> $filters
     */
    protected function applyPaymentFilters(Builder $query, array $filters): void
    {
        $keyword = trim((string) ($filters['search'] ?? ''));

        if ($keyword !== '') {
            $query->where(function (Builder $builder) use ($keyword): void {
                $builder
                    ->where('payment_code', 'like', "%{$keyword}%")
                    ->orWhere('transaction_reference', 'like', "%{$keyword}%")
                    ->orWhere('midtrans_order_id', 'like', "%{$keyword}%")
                    ->orWhere('notes', 'like', "%{$keyword}%")
                    ->orWhereHas('reservation', function (Builder $reservationQuery) use ($keyword): void {
                        $reservationQuery
                            ->where('reservation_code', 'like', "%{$keyword}%")
                            ->orWhere('customer_name', 'like', "%{$keyword}%")
                            ->orWhere('customer_phone', 'like', "%{$keyword}%");
                    });
            });
        }

        $dateField = $filters['date_field'] ?? 'paid_at';

        $query
            ->when(isset($filters['status']), fn (Builder $builder) => $builder->where('status', $filters['status']))
            ->when(isset($filters['method']), fn (Builder $builder) => $builder->where('method', $filters['method']))
            ->when(isset($filters['type']), fn (Builder $builder) => $builder->where('type', $filters['type']))
            ->when(isset($filters['min_amount']), fn (Builder $builder) => $builder->where('amount', '>=', $filters['min_amount']))
            ->when(isset($filters['date_from']), fn (Builder $builder) => $builder->whereDate($dateField, '>=', $filters['date_from']))
            ->when(isset($filters['date_until']), fn (Builder $builder) => $builder->whereDate($dateField, '<=', $filters['date_until']));
    }

    protected function profile(): ?CafeProfile
    {
        return CafeProfile::query()->first();
    }

    protected function rupiah(float|int|string|null $amount): string
    {
        return 'Rp '.number_format((float) $amount, 0, ',', '.');
    }

    protected function formatDate(?string $value, string $fallback): string
    {
        if (! filled($value)) {
            return $fallback;
        }

        return Carbon::parse($value)->translatedFormat('d M Y');
    }

    /**
     * @return array<string, string>
     */
    protected function paymentDateFieldLabels(): array
    {
        return [
            'created_at' => 'Tanggal dibuat',
            'paid_at' => 'Tanggal dibayar',
            'verified_at' => 'Tanggal diverifikasi',
        ];
    }
}
