<?php

namespace Tests\Feature\CafeReservation;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\ReservationStatus;
use App\Enums\TableStatus;
use App\Models\CafeProfile;
use App\Models\CafeTable;
use App\Models\ReservationSlot;
use App\Models\User;
use App\Services\CafeReservation\CafeAvailabilityService;
use App\Services\CafeReservation\CafeReservationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CafeReservationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_reservation_with_automatic_table_assignment_and_down_payment(): void
    {
        CafeProfile::factory()->create([
            'down_payment_amount' => 50000,
        ]);

        User::factory()->admin()->create();
        User::factory()->staff()->create();
        $customer = User::factory()->customer()->create();

        $smallTable = CafeTable::factory()->create([
            'code' => 'A1',
            'capacity' => 2,
        ]);

        $mediumTable = CafeTable::factory()->create([
            'code' => 'B1',
            'capacity' => 4,
        ]);

        CafeTable::factory()->create([
            'code' => 'C1',
            'capacity' => 6,
        ]);

        $date = Carbon::create(2026, 5, 18);
        $slot = $this->createSlot($date, '18:00:00', '20:00:00', 'Dinner');

        $result = app(CafeReservationService::class)->createReservation([
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone_number,
            'reservation_date' => $date->toDateString(),
            'start_time' => '18:00',
            'guest_count' => 3,
            'notes' => 'Meeting kecil.',
            'payment' => [
                'method' => PaymentMethod::Qris,
            ],
        ]);

        $reservation = $result['reservation'];
        $payment = $result['payment'];

        $this->assertSame($slot->id, $reservation->reservation_slot_id);
        $this->assertSame($mediumTable->id, $reservation->cafe_table_id);
        $this->assertNotSame($smallTable->id, $reservation->cafe_table_id);
        $this->assertSame(ReservationStatus::PendingPayment, $reservation->status);
        $this->assertSame('18:00:00', $reservation->start_time);
        $this->assertSame('20:00:00', $reservation->end_time);
        $this->assertSame('50000.00', $reservation->amount_due);
        $this->assertNotNull($payment);
        $this->assertSame(PaymentType::DownPayment, $payment->type);
        $this->assertSame(PaymentStatus::Pending, $payment->status);
        $this->assertSame(PaymentMethod::Qris, $payment->method);
        $this->assertSame('50000.00', $payment->amount);
        $this->assertSame(TableStatus::Reserved, $result['table']->status);
        $this->assertCount(2, $result['notifications'][0]['admins']);
    }

    public function test_it_confirms_a_reservation_immediately_when_payment_is_skipped(): void
    {
        CafeProfile::factory()->create([
            'down_payment_amount' => 50000,
        ]);

        User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        CafeTable::factory()->create([
            'capacity' => 4,
        ]);

        $date = Carbon::create(2026, 5, 19);
        $this->createSlot($date, '13:00:00', '15:00:00', 'Lunch');

        $result = app(CafeReservationService::class)->createReservation([
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone_number,
            'reservation_date' => $date->toDateString(),
            'start_time' => '13:00',
            'guest_count' => 2,
            'payment' => [
                'type' => null,
            ],
        ]);

        $reservation = $result['reservation'];

        $this->assertNull($result['payment']);
        $this->assertSame(ReservationStatus::Confirmed, $reservation->status);
        $this->assertNotNull($reservation->confirmed_at);
        $this->assertSame('0.00', $reservation->amount_due);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_it_marks_a_reservation_cancelled_and_releases_the_table(): void
    {
        CafeProfile::factory()->create([
            'down_payment_amount' => 0,
        ]);

        User::factory()->staff()->create();
        $customer = User::factory()->customer()->create();
        $admin = User::factory()->admin()->create();

        $table = CafeTable::factory()->create([
            'code' => 'A1',
            'capacity' => 4,
        ]);

        $date = Carbon::create(2026, 5, 20);
        $this->createSlot($date, '10:00:00', '12:00:00', 'Brunch');

        $created = app(CafeReservationService::class)->createReservation([
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone_number,
            'reservation_date' => $date->toDateString(),
            'start_time' => '10:00',
            'guest_count' => 2,
            'cafe_table_id' => $table->id,
        ]);

        $result = app(CafeReservationService::class)->cancelReservation(
            $created['reservation'],
            'Pelanggan berhalangan hadir.',
            $admin,
        );

        $reservation = $result['reservation'];

        $this->assertSame(ReservationStatus::Cancelled, $reservation->status);
        $this->assertSame('Pelanggan berhalangan hadir.', $reservation->cancellation_reason);
        $this->assertSame($admin->id, $reservation->cancelled_by);
        $this->assertNotNull($reservation->cancelled_at);
        $this->assertSame(TableStatus::Available, $table->fresh()->status);
    }

    public function test_it_reschedules_a_reservation_and_assigns_a_bigger_table_when_needed(): void
    {
        CafeProfile::factory()->create([
            'down_payment_amount' => 0,
        ]);

        User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        $smallTable = CafeTable::factory()->create([
            'code' => 'A1',
            'capacity' => 2,
        ]);

        $largeTable = CafeTable::factory()->create([
            'code' => 'B1',
            'capacity' => 4,
        ]);

        $firstDate = Carbon::create(2026, 5, 21);
        $secondDate = Carbon::create(2026, 5, 22);
        $firstSlot = $this->createSlot($firstDate, '18:00:00', '20:00:00', 'Dinner');
        $secondSlot = $this->createSlot($secondDate, '13:00:00', '15:00:00', 'Lunch');

        $created = app(CafeReservationService::class)->createReservation([
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone_number,
            'reservation_date' => $firstDate->toDateString(),
            'start_time' => '18:00',
            'guest_count' => 2,
        ]);

        $result = app(CafeReservationService::class)->rescheduleReservation(
            $created['reservation'],
            [
                'reservation_date' => $secondDate->toDateString(),
                'start_time' => '13:00',
                'guest_count' => 4,
            ],
        );

        $reservation = $result['reservation'];

        $this->assertSame($secondSlot->id, $reservation->reservation_slot_id);
        $this->assertSame($largeTable->id, $reservation->cafe_table_id);
        $this->assertSame('13:00:00', $reservation->start_time);
        $this->assertSame('15:00:00', $reservation->end_time);
        $this->assertSame(4, $reservation->guest_count);
        $this->assertSame(TableStatus::Available, $smallTable->fresh()->status);
        $this->assertSame(TableStatus::Reserved, $largeTable->fresh()->status);
        $this->assertSame(
            $firstDate->toDateString(),
            $result['notifications'][0]['context']['previous_schedule']['reservation_date'],
        );
        $this->assertSame(
            $firstSlot->start_time,
            $result['notifications'][0]['context']['previous_schedule']['start_time'],
        );
    }

    public function test_it_reports_unavailability_when_all_matching_tables_are_booked(): void
    {
        CafeProfile::factory()->create([
            'down_payment_amount' => 0,
        ]);

        $customer = User::factory()->customer()->create();
        $table = CafeTable::factory()->create([
            'capacity' => 4,
        ]);

        $date = Carbon::create(2026, 5, 23);
        $slot = $this->createSlot($date, '18:00:00', '20:00:00', 'Dinner');

        app(CafeReservationService::class)->createReservation([
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone_number,
            'reservation_date' => $date->toDateString(),
            'start_time' => '18:00',
            'guest_count' => 2,
            'cafe_table_id' => $table->id,
        ]);

        $availability = app(CafeAvailabilityService::class)->checkAvailability(
            $date->toDateString(),
            $slot->start_time,
            2,
        );

        $this->assertFalse($availability['is_available']);
        $this->assertCount(1, $availability['conflicting_reservations']);
        $this->assertSame($table->id, $availability['conflicting_reservations']->first()->cafe_table_id);
        $this->assertTrue($availability['available_tables']->isEmpty());
    }

    protected function createSlot(
        Carbon $date,
        string $startTime,
        string $endTime,
        string $name,
    ): ReservationSlot {
        return ReservationSlot::factory()->create([
            'name' => $name,
            'day_of_week' => $date->dayOfWeek,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_active' => true,
        ]);
    }
}
