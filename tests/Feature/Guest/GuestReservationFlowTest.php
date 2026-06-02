<?php

namespace Tests\Feature\Guest;

use App\Enums\PaymentMethod;
use App\Models\CafeProfile;
use App\Models\CafeTable;
use App\Models\ReservationSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_pages_render_from_controllers_and_alias_slug_redirects(): void
    {
        CafeProfile::factory()->create();

        $slot = ReservationSlot::factory()->create([
            'day_of_week' => now()->addDay()->dayOfWeek,
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
        ]);

        CafeTable::factory()->create([
            'capacity' => 4,
            'is_active' => true,
        ]);

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('Cafe Amiko');

        $this->get(route('packages.index'))
            ->assertOk()
            ->assertSee('Filter Reservasi');

        $this->get(route('booking.show', ['slug' => 'coffee-date-corner']))
            ->assertOk()
            ->assertSee('Coffee Date Corner');

        $this->get('/booking/family-portrait-signature')
            ->assertRedirect(route('booking.show', ['slug' => 'coffee-date-corner']));
    }

    public function test_package_filter_works(): void
    {
        CafeProfile::factory()->create();

        $response = $this->get(route('packages.index', [
            'q' => 'Work',
            'category' => 'Work Space',
        ]));

        $response->assertOk()
            ->assertSee('Work & Brew Table')
            ->assertDontSee('Coffee Date Corner');
    }

    public function test_authenticated_customer_can_create_reservation_from_guest_booking_page(): void
    {
        CafeProfile::factory()->create([
            'down_payment_amount' => 50000,
        ]);

        $customer = User::factory()->customer()->create([
            'name' => 'Pelanggan Booking',
            'phone_number' => '081234567890',
        ]);

        CafeTable::factory()->create([
            'name' => 'Meja Test',
            'capacity' => 4,
            'is_active' => true,
        ]);

        ReservationSlot::factory()->create([
            'day_of_week' => now()->addDay()->dayOfWeek,
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($customer)->post(route('booking.store', ['slug' => 'coffee-date-corner']), [
            'customer_name' => 'Pelanggan Booking',
            'customer_phone' => '081234567890',
            'reservation_date' => now()->addDay()->toDateString(),
            'start_time' => '10:00',
            'guest_count' => 2,
            'payment_method' => PaymentMethod::Cash->value,
            'notes' => 'Butuh area nyaman',
        ]);

        $response->assertRedirect(route('customer.profile'));

        $this->assertDatabaseHas('reservations', [
            'user_id' => $customer->id,
            'package_slug' => 'coffee-date-corner',
            'package_name' => 'Coffee Date Corner',
            'customer_name' => 'Pelanggan Booking',
        ]);

        $this->assertDatabaseCount('payments', 1);
    }

    public function test_guest_can_submit_review_for_booking_package(): void
    {
        $response = $this->post(route('booking.reviews.store', ['slug' => 'coffee-date-corner']), [
            'guest_name' => 'Reviewer Umum',
            'rating' => 5,
            'comment' => 'Tempatnya nyaman dan proses reservasinya jelas.',
        ]);

        $response->assertRedirect(route('booking.show', ['slug' => 'coffee-date-corner']).'#reviews');

        $this->assertDatabaseHas('guest_reviews', [
            'package_slug' => 'coffee-date-corner',
            'guest_name' => 'Reviewer Umum',
            'rating' => 5,
        ]);
    }
}
