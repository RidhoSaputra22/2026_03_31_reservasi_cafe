<?php

namespace Tests\Feature\Customer;

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
}
