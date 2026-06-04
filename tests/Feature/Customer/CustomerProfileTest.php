<?php

namespace Tests\Feature\Customer;

use App\Enums\PaymentType;
use App\Models\CafeTable;
use App\Models\CafeProfile;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\ReservationSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_customer_login(): void
    {
        $this->get(route('customer.profile'))
            ->assertRedirect(route('login'));
    }

    public function test_customer_can_view_profile_with_reservation_history_and_reminder(): void
    {
        CafeProfile::factory()->create();

        $customer = User::factory()->customer()->create([
            'name' => 'Bima Pelanggan',
            'email' => 'bima@example.test',
        ]);

        $table = CafeTable::factory()->create([
            'name' => 'Meja Window',
            'capacity' => 4,
        ]);

        $slot = ReservationSlot::factory()->create([
            'day_of_week' => now()->addDay()->dayOfWeek,
            'start_time' => '18:00:00',
            'end_time' => '20:00:00',
        ]);

        $reservation = Reservation::factory()
            ->confirmed()
            ->for($customer)
            ->for($table, 'cafeTable')
            ->for($slot, 'reservationSlot')
            ->create([
                'reservation_code' => 'RSV-PROFILE',
                'customer_name' => 'Bima Pelanggan',
                'reservation_date' => now()->addDay()->toDateString(),
                'start_time' => '18:00:00',
                'end_time' => '20:00:00',
                'guest_count' => 3,
                'amount_due' => 50000,
            ]);

        Payment::factory()
            ->paid()
            ->for($reservation)
            ->create([
                'amount' => 50000,
            ]);

        $this->actingAs($customer)
            ->get(route('customer.profile'))
            ->assertOk()
            ->assertSee('Halo, Bima Pelanggan')
            ->assertSee('Reservasi Terdekat')
            ->assertSee('Riwayat Reservasi')
            ->assertSee('RSV-PROFILE')
            ->assertSee('Meja Window');
    }

    public function test_admin_is_redirected_to_dashboard_from_customer_profile(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('customer.profile'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_customer_profile_shows_pending_payment_deadline(): void
    {
        config([
            'reservations.pending_payment_timeout_minutes' => 60,
        ]);

        CafeProfile::factory()->create();

        $customer = User::factory()->customer()->create([
            'name' => 'Dina Pelanggan',
            'email' => 'dina@example.test',
        ]);

        $table = CafeTable::factory()->create([
            'name' => 'Meja A2',
            'capacity' => 2,
        ]);

        $slot = ReservationSlot::factory()->create([
            'day_of_week' => now()->addDay()->dayOfWeek,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
        ]);

        $reservation = Reservation::factory()
            ->pendingPayment()
            ->for($customer)
            ->for($table, 'cafeTable')
            ->for($slot, 'reservationSlot')
            ->create([
                'reservation_code' => 'RSV-PENDING',
                'customer_name' => 'Dina Pelanggan',
                'reservation_date' => now()->addDay()->toDateString(),
                'start_time' => '08:00:00',
                'end_time' => '09:00:00',
                'guest_count' => 2,
                'amount_due' => 50000,
            ]);

        Payment::factory()
            ->pending()
            ->for($reservation)
            ->create([
                'amount' => 50000,
                'snap_token' => 'snap-token-profile',
                'midtrans_order_id' => 'ORDER-PROFILE-1',
                'created_at' => now()->subMinutes(10),
                'updated_at' => now()->subMinutes(10),
            ]);

        $this->actingAs($customer)
            ->get(route('customer.profile'))
            ->assertOk()
            ->assertSee('RSV-PENDING')
            ->assertSee('Selesaikan pembayaran sebelum')
            ->assertSee('Lanjut Pembayaran');
    }

    public function test_customer_profile_hides_reservations_cancelled_due_to_expired_payment(): void
    {
        config([
            'reservations.expired_payment_cancellation_reason' => 'Reservasi dibatalkan otomatis karena pembayaran melewati batas waktu.',
        ]);

        CafeProfile::factory()->create();

        $customer = User::factory()->customer()->create([
            'name' => 'Raka Pelanggan',
            'email' => 'raka@example.test',
        ]);

        $table = CafeTable::factory()->create([
            'name' => 'Meja A4',
            'capacity' => 2,
        ]);

        $slot = ReservationSlot::factory()->create([
            'day_of_week' => now()->addDay()->dayOfWeek,
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
        ]);

        $reservation = Reservation::factory()
            ->cancelled()
            ->for($customer)
            ->for($table, 'cafeTable')
            ->for($slot, 'reservationSlot')
            ->create([
                'reservation_code' => 'RSV-EXPIRED-HIDDEN',
                'customer_name' => 'Raka Pelanggan',
                'reservation_date' => now()->addDay()->toDateString(),
                'start_time' => '09:00:00',
                'end_time' => '10:00:00',
                'guest_count' => 2,
                'amount_due' => 50000,
                'cancellation_reason' => Reservation::expiredPaymentCancellationReason(),
            ]);

        Payment::factory()
            ->failed()
            ->for($reservation)
            ->create([
                'amount' => 50000,
            ]);

        $this->actingAs($customer)
            ->get(route('customer.profile'))
            ->assertOk()
            ->assertDontSee('RSV-EXPIRED-HIDDEN')
            ->assertSee('Belum ada reservasi.');
    }

    public function test_customer_cannot_cancel_reservation_after_remaining_payment_has_been_paid(): void
    {
        CafeProfile::factory()->create();

        $customer = User::factory()->customer()->create([
            'name' => 'Salsa Pelanggan',
            'email' => 'salsa@example.test',
        ]);

        $table = CafeTable::factory()->create([
            'name' => 'Meja A1',
            'capacity' => 2,
        ]);

        $slot = ReservationSlot::factory()->create([
            'day_of_week' => now()->addDay()->dayOfWeek,
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
        ]);

        $reservation = Reservation::factory()
            ->confirmed()
            ->for($customer)
            ->for($table, 'cafeTable')
            ->for($slot, 'reservationSlot')
            ->create([
                'reservation_code' => 'RSV-LUNAS',
                'customer_name' => 'Salsa Pelanggan',
                'reservation_date' => now()->addDay()->toDateString(),
                'start_time' => '08:00:00',
                'end_time' => '12:00:00',
                'guest_count' => 2,
                'total_price' => 250000,
                'amount_due' => 0,
            ]);

        $downPayment = Payment::factory()
            ->paid()
            ->for($reservation)
            ->create([
                'type' => PaymentType::DownPayment->value,
                'amount' => 50000,
            ]);

        Payment::factory()
            ->paid()
            ->for($reservation)
            ->create([
                'parent_payment_id' => $downPayment->id,
                'type' => PaymentType::FullPayment->value,
                'amount' => 200000,
            ]);

        $this->actingAs($customer)
            ->get(route('customer.profile'))
            ->assertOk()
            ->assertSee('RSV-LUNAS')
            ->assertDontSee('Batalkan Reservasi');
    }

    public function test_customer_can_view_payment_qr_page_for_paid_down_payment(): void
    {
        CafeProfile::factory()->create();

        $customer = User::factory()->customer()->create([
            'name' => 'Nadia Pelanggan',
            'email' => 'nadia@example.test',
        ]);

        $table = CafeTable::factory()->create([
            'name' => 'Meja QR',
            'capacity' => 2,
        ]);

        $slot = ReservationSlot::factory()->create([
            'day_of_week' => now()->addDay()->dayOfWeek,
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
        ]);

        $reservation = Reservation::factory()
            ->confirmed()
            ->for($customer)
            ->for($table, 'cafeTable')
            ->for($slot, 'reservationSlot')
            ->create([
                'reservation_code' => 'RSV-QR-001',
                'customer_name' => 'Nadia Pelanggan',
                'reservation_date' => now()->addDay()->toDateString(),
                'start_time' => '10:00:00',
                'end_time' => '11:00:00',
                'guest_count' => 2,
                'total_price' => 250000,
                'amount_due' => 200000,
            ]);

        Payment::factory()
            ->paid()
            ->for($reservation)
            ->create([
                'payment_code' => 'PAY-QR-001',
                'type' => PaymentType::DownPayment->value,
                'amount' => 50000,
            ]);

        $this->actingAs($customer)
            ->get(route('customer.payments.qr'))
            ->assertOk()
            ->assertSee('QR Paket Sudah DP')
            ->assertSee('RSV-QR-001')
            ->assertSee('PAY-QR-001')
            ->assertSee('Status Pelunasan')
            ->assertSee('Belum Dilunasi')
            ->assertSee('Isi QR')
            ->assertDontSee(route('admin.payments.index', ['search' => 'RSV-QR-001']), false);
    }

    public function test_customer_payment_qr_page_only_shows_requested_reservation(): void
    {
        CafeProfile::factory()->create();

        $customer = User::factory()->customer()->create([
            'name' => 'Ayu Pelanggan',
            'email' => 'ayu@example.test',
        ]);

        $table = CafeTable::factory()->create([
            'name' => 'Meja QR Pilihan',
            'capacity' => 2,
        ]);

        $slot = ReservationSlot::factory()->create([
            'day_of_week' => now()->addDay()->dayOfWeek,
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
        ]);

        $firstReservation = Reservation::factory()
            ->confirmed()
            ->for($customer)
            ->for($table, 'cafeTable')
            ->for($slot, 'reservationSlot')
            ->create([
                'reservation_code' => 'RSV-QR-A',
                'customer_name' => 'Ayu Pelanggan',
                'reservation_date' => now()->addDay()->toDateString(),
                'start_time' => '10:00:00',
                'end_time' => '11:00:00',
                'guest_count' => 2,
                'total_price' => 250000,
                'amount_due' => 200000,
            ]);

        $secondReservation = Reservation::factory()
            ->confirmed()
            ->for($customer)
            ->for($table, 'cafeTable')
            ->for($slot, 'reservationSlot')
            ->create([
                'reservation_code' => 'RSV-QR-B',
                'customer_name' => 'Ayu Pelanggan',
                'reservation_date' => now()->addDays(2)->toDateString(),
                'start_time' => '12:00:00',
                'end_time' => '13:00:00',
                'guest_count' => 2,
                'total_price' => 300000,
                'amount_due' => 250000,
            ]);

        Payment::factory()
            ->paid()
            ->for($firstReservation)
            ->create([
                'payment_code' => 'PAY-QR-A',
                'type' => PaymentType::DownPayment->value,
                'amount' => 50000,
            ]);

        Payment::factory()
            ->paid()
            ->for($secondReservation)
            ->create([
                'payment_code' => 'PAY-QR-B',
                'type' => PaymentType::DownPayment->value,
                'amount' => 50000,
            ]);

        $this->actingAs($customer)
            ->get(route('customer.payments.qr', ['reservation' => $secondReservation->id]))
            ->assertOk()
            ->assertSee('RSV-QR-B')
            ->assertSee('PAY-QR-B')
            ->assertDontSee('RSV-QR-A')
            ->assertDontSee('PAY-QR-A');
    }

    public function test_customer_payment_qr_page_shows_paid_off_status_when_settlement_is_complete(): void
    {
        CafeProfile::factory()->create();

        $customer = User::factory()->customer()->create([
            'name' => 'Lina Pelanggan',
            'email' => 'lina@example.test',
        ]);

        $table = CafeTable::factory()->create([
            'name' => 'Meja QR Lunas',
            'capacity' => 2,
        ]);

        $slot = ReservationSlot::factory()->create([
            'day_of_week' => now()->addDay()->dayOfWeek,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
        ]);

        $reservation = Reservation::factory()
            ->confirmed()
            ->for($customer)
            ->for($table, 'cafeTable')
            ->for($slot, 'reservationSlot')
            ->create([
                'reservation_code' => 'RSV-QR-LUNAS',
                'customer_name' => 'Lina Pelanggan',
                'reservation_date' => now()->addDay()->toDateString(),
                'start_time' => '08:00:00',
                'end_time' => '10:00:00',
                'guest_count' => 2,
                'total_price' => 250000,
                'amount_due' => 0,
            ]);

        $downPayment = Payment::factory()
            ->paid()
            ->for($reservation)
            ->create([
                'payment_code' => 'PAY-QR-DP-LUNAS',
                'type' => PaymentType::DownPayment->value,
                'amount' => 50000,
            ]);

        Payment::factory()
            ->paid()
            ->for($reservation)
            ->create([
                'payment_code' => 'PAY-QR-SISA-LUNAS',
                'parent_payment_id' => $downPayment->id,
                'type' => PaymentType::FullPayment->value,
                'amount' => 200000,
            ]);

        $this->actingAs($customer)
            ->get(route('customer.payments.qr', ['reservation' => $reservation->id]))
            ->assertOk()
            ->assertSee('RSV-QR-LUNAS')
            ->assertSee('PAY-QR-DP-LUNAS')
            ->assertSee('Status Pelunasan')
            ->assertSee('Sudah Dilunasi');
    }
}
