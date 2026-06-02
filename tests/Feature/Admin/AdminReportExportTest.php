<?php

namespace Tests\Feature\Admin;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\ReservationStatus;
use App\Models\CafeProfile;
use App\Models\CafeTable;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\ReservationSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_reservation_history_to_pdf(): void
    {
        CafeProfile::factory()->create();

        $admin = User::factory()->admin()->create();
        $table = CafeTable::factory()->create([
            'code' => 'A-12',
            'name' => 'Meja Window Besar',
        ]);
        $slot = ReservationSlot::factory()->create([
            'name' => 'Dinner',
        ]);

        Reservation::factory()
            ->for($table, 'cafeTable')
            ->for($slot, 'reservationSlot')
            ->create([
                'reservation_code' => 'RSV-EXPORT-01',
                'customer_name' => 'Laporan Reservasi',
                'guest_count' => 4,
                'reservation_date' => now()->toDateString(),
                'status' => ReservationStatus::Confirmed->value,
                'amount_due' => 120000,
            ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.reservations.pdf', [
            'search' => 'EXPORT-01',
            'status' => ReservationStatus::Confirmed->value,
            'cafe_table_id' => $table->id,
            'min_guest_count' => 4,
            'date_from' => now()->toDateString(),
            'date_until' => now()->toDateString(),
        ]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('laporan-reservasi', (string) $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF', (string) $response->getContent());
    }

    public function test_admin_can_export_payment_history_to_pdf(): void
    {
        CafeProfile::factory()->create();

        $admin = User::factory()->admin()->create();
        $reservation = Reservation::factory()->create([
            'reservation_code' => 'RSV-PAY-01',
            'customer_name' => 'Laporan Pembayaran',
        ]);

        Payment::factory()
            ->for($reservation)
            ->create([
                'payment_code' => 'PAY-EXPORT-01',
                'type' => PaymentType::FullPayment->value,
                'method' => PaymentMethod::Qris->value,
                'status' => PaymentStatus::Paid->value,
                'amount' => 150000,
                'paid_at' => now()->subHour(),
                'verified_at' => now(),
            ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.payments.pdf', [
            'search' => 'PAY-EXPORT-01',
            'date_field' => 'paid_at',
            'status' => PaymentStatus::Paid->value,
            'method' => PaymentMethod::Qris->value,
            'type' => PaymentType::FullPayment->value,
            'min_amount' => 100000,
            'date_from' => now()->subDay()->toDateString(),
            'date_until' => now()->toDateString(),
        ]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('laporan-pembayaran', (string) $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF', (string) $response->getContent());
    }
}
