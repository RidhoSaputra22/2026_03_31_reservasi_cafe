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
use App\Services\CafeReservation\CafePaymentService;
use App\Services\CafeReservation\CafeReservationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
            'duration_hours' => 2,
            'guest_count' => 3,
            'notes' => 'Meeting kecil.',
            'payment' => [
                'method' => PaymentMethod::Cash,
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
        $this->assertSame(2, $reservation->duration_hours);
        $this->assertSame('50000.00', $reservation->amount_due);
        $this->assertNotNull($payment);
        $this->assertSame(PaymentType::DownPayment, $payment->type);
        $this->assertSame(PaymentStatus::Pending, $payment->status);
        $this->assertSame(PaymentMethod::Cash, $payment->method);
        $this->assertSame('50000.00', $payment->amount);
        $this->assertSame(TableStatus::Reserved, $result['table']->status);
        $this->assertCount(2, $result['notifications'][0]['admins']);
    }

    public function test_it_creates_a_midtrans_snap_transaction_for_online_payment(): void
    {
        config([
            'services.midtrans.server_key' => 'SB-Mid-server-test',
            'services.midtrans.is_production' => false,
            'services.midtrans.enabled_payments' => ['qris'],
            'services.midtrans.finish_url' => 'https://example.test/midtrans/finish',
        ]);

        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'snap-token-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123',
            ], 201),
        ]);

        CafeProfile::factory()->create([
            'down_payment_amount' => 50000,
        ]);

        User::factory()->admin()->create();
        $customer = User::factory()->customer()->create([
            'name' => 'Iskandar Hidayat',
            'email' => 'iskandar@example.test',
            'phone_number' => '081234567890',
        ]);

        CafeTable::factory()->create([
            'capacity' => 4,
        ]);

        $date = Carbon::create(2026, 5, 24);
        $this->createSlot($date, '19:00:00', '21:00:00', 'Dinner');

        $result = app(CafeReservationService::class)->createReservation([
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone_number,
            'reservation_date' => $date->toDateString(),
            'start_time' => '19:00',
            'duration_hours' => 2,
            'guest_count' => 2,
            'payment' => [
                'method' => PaymentMethod::Qris,
            ],
        ]);

        $payment = $result['payment']->fresh();

        $this->assertSame($payment->transaction_reference, $payment->midtrans_order_id);
        $this->assertSame('snap-token-123', $payment->snap_token);
        $this->assertSame('https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123', $payment->snap_redirect_url);
        $this->assertSame(PaymentStatus::Pending->value, $payment->midtrans_status);
        $this->assertSame('snap-token-123', $payment->midtrans_payload['token']);
        $this->assertSame('snap-token-123', $result['notifications'][0]['context']['payment']['snap_token']);

        Http::assertSent(function ($request) use ($payment, $customer): bool {
            $payload = $request->data();

            return $request->method() === 'POST'
                && $request->url() === 'https://app.sandbox.midtrans.com/snap/v1/transactions'
                && $payload['transaction_details']['order_id'] === $payment->transaction_reference
                && $payload['transaction_details']['gross_amount'] === 50000
                && $payload['enabled_payments'] === ['qris']
                && $payload['customer_details']['email'] === $customer->email
                && $payload['callbacks']['finish'] === 'https://example.test/midtrans/finish'
                && $payload['expiry']['duration'] === 60
                && $payload['expiry']['unit'] === 'minute'
                && str_ends_with($payload['expiry']['start_time'], '+0700')
                && $payload['page_expiry']['duration'] === 60
                && $payload['page_expiry']['unit'] === 'minute';
        });
    }

    public function test_it_creates_a_reservation_with_full_payment_when_amount_is_explicitly_requested(): void
    {
        CafeProfile::factory()->create([
            'down_payment_amount' => 50000,
        ]);

        User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        CafeTable::factory()->create([
            'capacity' => 4,
        ]);

        $date = Carbon::create(2026, 5, 26);
        $this->createSlot($date, '10:00:00', '12:00:00', 'Brunch');

        $result = app(CafeReservationService::class)->createReservation([
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone_number,
            'reservation_date' => $date->toDateString(),
            'start_time' => '10:00',
            'duration_hours' => 2,
            'guest_count' => 2,
            'payment' => [
                'type' => PaymentType::FullPayment,
                'method' => PaymentMethod::Cash,
                'amount' => 150000,
            ],
        ]);

        $reservation = $result['reservation'];
        $payment = $result['payment'];

        $this->assertNotNull($payment);
        $this->assertSame(PaymentType::FullPayment, $payment->type);
        $this->assertSame('150000.00', $payment->amount);
        $this->assertSame('150000.00', $reservation->amount_due);
        $this->assertSame(PaymentMethod::Cash, $payment->method);
        $this->assertSame(PaymentStatus::Pending, $payment->status);
    }

    public function test_it_can_store_a_shorter_duration_than_the_full_slot_window(): void
    {
        CafeProfile::factory()->create([
            'down_payment_amount' => 0,
        ]);

        $customer = User::factory()->customer()->create();

        CafeTable::factory()->create([
            'capacity' => 4,
        ]);

        $date = Carbon::create(2026, 5, 27);
        $this->createSlot($date, '10:00:00', '12:00:00', 'Brunch');

        $result = app(CafeReservationService::class)->createReservation([
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone_number,
            'reservation_date' => $date->toDateString(),
            'start_time' => '10:00',
            'duration_hours' => 1,
            'guest_count' => 2,
            'payment' => [
                'type' => null,
            ],
        ]);

        $reservation = $result['reservation'];

        $this->assertSame('10:00:00', $reservation->start_time);
        $this->assertSame('11:00:00', $reservation->end_time);
    }

    public function test_it_syncs_payment_and_reservation_from_midtrans_notification(): void
    {
        config([
            'services.midtrans.server_key' => 'SB-Mid-server-test',
            'services.midtrans.is_production' => false,
        ]);

        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'snap-token-456',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-456',
            ], 201),
        ]);

        CafeProfile::factory()->create([
            'down_payment_amount' => 50000,
        ]);

        User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        CafeTable::factory()->create([
            'capacity' => 4,
        ]);

        $date = Carbon::create(2026, 5, 25);
        $this->createSlot($date, '20:00:00', '22:00:00', 'Dinner');

        $created = app(CafeReservationService::class)->createReservation([
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone_number,
            'reservation_date' => $date->toDateString(),
            'start_time' => '20:00',
            'duration_hours' => 2,
            'guest_count' => 2,
            'payment' => [
                'method' => PaymentMethod::Qris,
            ],
        ]);

        $payment = $created['payment']->fresh();
        $payload = [
            'order_id' => $payment->midtrans_order_id,
            'status_code' => '200',
            'gross_amount' => '50000.00',
            'transaction_status' => 'settlement',
            'settlement_time' => '2026-05-25 20:10:00',
        ];
        $payload['signature_key'] = hash(
            'sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].'SB-Mid-server-test',
        );

        $updatedPayment = app(CafePaymentService::class)->syncFromMidtransNotification($payload);

        $this->assertSame(PaymentStatus::Paid, $updatedPayment->status);
        $this->assertNotNull($updatedPayment->paid_at);
        $this->assertNotNull($updatedPayment->verified_at);
        $this->assertSame('settlement', $updatedPayment->midtrans_status);
        $this->assertSame(ReservationStatus::Confirmed, $updatedPayment->reservation->status);
        $this->assertNotNull($updatedPayment->reservation->confirmed_at);
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
            'duration_hours' => 2,
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
            'duration_hours' => 2,
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

    public function test_it_auto_cancels_expired_pending_payment_reservations(): void
    {
        config([
            'reservations.pending_payment_timeout_minutes' => 60,
            'reservations.expired_payment_cancellation_reason' => 'Reservasi dibatalkan otomatis karena pembayaran melewati batas waktu.',
        ]);

        CafeProfile::factory()->create([
            'down_payment_amount' => 50000,
        ]);

        $customer = User::factory()->customer()->create();

        $table = CafeTable::factory()->create([
            'code' => 'A2',
            'capacity' => 4,
        ]);

        $date = Carbon::create(2026, 5, 28);
        $this->createSlot($date, '15:00:00', '17:00:00', 'Afternoon');

        $created = app(CafeReservationService::class)->createReservation([
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone_number,
            'reservation_date' => $date->toDateString(),
            'start_time' => '15:00',
            'duration_hours' => 2,
            'guest_count' => 2,
            'cafe_table_id' => $table->id,
            'payment' => [
                'method' => PaymentMethod::Cash,
            ],
        ]);

        $payment = $created['payment'];

        $payment->forceFill([
            'created_at' => now()->subMinutes(61),
            'updated_at' => now()->subMinutes(61),
        ])->save();

        $expiredReservations = app(CafeReservationService::class)->expireTimedOutPendingReservations();

        $reservation = $created['reservation']->fresh(['latestPayment']);

        $this->assertSame(1, $expiredReservations);
        $this->assertSame(ReservationStatus::Cancelled, $reservation->status);
        $this->assertSame(
            'Reservasi dibatalkan otomatis karena pembayaran melewati batas waktu.',
            $reservation->cancellation_reason,
        );
        $this->assertNotNull($reservation->cancelled_at);
        $this->assertSame(PaymentStatus::Failed, $reservation->latestPayment->status);
        $this->assertSame(TableStatus::Available, $table->fresh()->status);
    }

    public function test_it_auto_cancels_expired_midtrans_reservations_when_midtrans_reports_missing_transaction(): void
    {
        config([
            'services.midtrans.server_key' => 'SB-Mid-server-test',
            'services.midtrans.is_production' => false,
            'services.midtrans.enabled_payments' => ['qris'],
            'reservations.pending_payment_timeout_minutes' => 60,
            'reservations.expired_payment_cancellation_reason' => 'Reservasi dibatalkan otomatis karena pembayaran melewati batas waktu.',
        ]);

        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'snap-token-expired',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-expired',
            ], 201),
            'https://api.sandbox.midtrans.com/v2/*/status' => Http::response([
                'status_code' => '404',
                'status_message' => "Transaction doesn't exist.",
            ], 200),
        ]);

        CafeProfile::factory()->create([
            'down_payment_amount' => 50000,
        ]);

        User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        $table = CafeTable::factory()->create([
            'code' => 'A2-MT',
            'capacity' => 4,
        ]);

        $date = Carbon::create(2026, 5, 28);
        $this->createSlot($date, '15:00:00', '17:00:00', 'Afternoon');

        $created = app(CafeReservationService::class)->createReservation([
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone_number,
            'reservation_date' => $date->toDateString(),
            'start_time' => '15:00',
            'duration_hours' => 2,
            'guest_count' => 2,
            'cafe_table_id' => $table->id,
            'payment' => [
                'method' => PaymentMethod::Qris,
            ],
        ]);

        $payment = $created['payment'];

        $payment->forceFill([
            'created_at' => now()->subMinutes(61),
            'updated_at' => now()->subMinutes(61),
        ])->save();

        $expiredReservations = app(CafeReservationService::class)->expireTimedOutPendingReservations();

        $reservation = $created['reservation']->fresh(['latestPayment']);

        $this->assertSame(1, $expiredReservations);
        $this->assertSame(ReservationStatus::Cancelled, $reservation->status);
        $this->assertSame(PaymentStatus::Failed, $reservation->latestPayment->status);
        $this->assertSame('failure', $reservation->latestPayment->midtrans_status);
        $this->assertSame(TableStatus::Available, $table->fresh()->status);
    }

    public function test_it_purges_expired_pending_payment_reservation_data(): void
    {
        config([
            'reservations.pending_payment_timeout_minutes' => 60,
            'reservations.expired_payment_cancellation_reason' => 'Reservasi dibatalkan otomatis karena pembayaran melewati batas waktu.',
        ]);

        CafeProfile::factory()->create([
            'down_payment_amount' => 50000,
        ]);

        $customer = User::factory()->customer()->create();

        $table = CafeTable::factory()->create([
            'code' => 'A3',
            'capacity' => 4,
        ]);

        $date = Carbon::create(2026, 5, 29);
        $this->createSlot($date, '15:00:00', '17:00:00', 'Afternoon');

        $created = app(CafeReservationService::class)->createReservation([
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone_number,
            'reservation_date' => $date->toDateString(),
            'start_time' => '15:00',
            'duration_hours' => 2,
            'guest_count' => 2,
            'cafe_table_id' => $table->id,
            'payment' => [
                'method' => PaymentMethod::Cash,
            ],
        ]);

        $payment = $created['payment'];

        $payment->forceFill([
            'created_at' => now()->subMinutes(61),
            'updated_at' => now()->subMinutes(61),
        ])->save();

        app(CafeReservationService::class)->expireTimedOutPendingReservations();

        $reservationId = $created['reservation']->id;
        $paymentId = $payment->id;

        $deletedReservations = app(CafeReservationService::class)->purgeExpiredPaymentReservations();

        $this->assertSame(1, $deletedReservations);
        $this->assertDatabaseMissing('reservations', [
            'id' => $reservationId,
        ]);
        $this->assertDatabaseMissing('payments', [
            'id' => $paymentId,
        ]);
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
            'duration_hours' => 2,
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
            'duration_hours' => 2,
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
