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
}
