<?php

namespace Database\Factories;

use App\Enums\ReservationStatus;
use App\Models\CafeTable;
use App\Models\Reservation;
use App\Models\ReservationSlot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $reservationDate = fake()->dateTimeBetween('+1 day', '+30 days');
        $startHour = fake()->randomElement([10, 13, 16, 18]);

        return [
            'reservation_code' => strtoupper(fake()->unique()->bothify('RSV-####??')),
            'user_id' => User::factory()->customer(),
            'cafe_table_id' => CafeTable::factory(),
            'reservation_slot_id' => ReservationSlot::factory(),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->numerify('08##########'),
            'reservation_date' => $reservationDate->format('Y-m-d'),
            'start_time' => sprintf('%02d:00:00', $startHour),
            'end_time' => sprintf('%02d:00:00', $startHour + 2),
            'guest_count' => fake()->numberBetween(1, 8),
            'notes' => fake()->optional()->sentence(),
            'amount_due' => fake()->randomFloat(2, 25000, 150000),
            'status' => ReservationStatus::PendingPayment->value,
            'confirmed_at' => null,
            'checked_in_at' => null,
            'completed_at' => null,
            'cancelled_at' => null,
            'cancellation_reason' => null,
            'confirmed_by' => null,
            'checked_in_by' => null,
            'cancelled_by' => null,
        ];
    }

    public function pendingPayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReservationStatus::PendingPayment->value,
        ]);
    }

    public function awaitingConfirmation(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReservationStatus::AwaitingConfirmation->value,
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReservationStatus::Confirmed->value,
            'confirmed_at' => now(),
        ]);
    }

    public function checkedIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'reservation_date' => now()->toDateString(),
            'status' => ReservationStatus::CheckedIn->value,
            'confirmed_at' => now()->subHour(),
            'checked_in_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'reservation_date' => now()->subDay()->toDateString(),
            'status' => ReservationStatus::Completed->value,
            'confirmed_at' => now()->subDays(2),
            'checked_in_at' => now()->subDay()->addHours(1),
            'completed_at' => now()->subDay()->addHours(3),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReservationStatus::Cancelled->value,
            'cancelled_at' => now(),
            'cancellation_reason' => 'Pelanggan membatalkan reservasi sebelum batas waktu.',
        ]);
    }
}
