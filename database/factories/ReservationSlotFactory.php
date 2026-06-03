<?php

namespace Database\Factories;

use App\Models\ReservationSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReservationSlot>
 */
class ReservationSlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startHour = fake()->numberBetween(8, 14);
        $durationHours = fake()->numberBetween(1, 3);
        $endHour = min(17, $startHour + $durationHours);

        return [
            'name' => fake()->randomElement(['Jam Operasional', 'Reguler', 'Reservasi Harian']),
            'day_of_week' => fake()->numberBetween(0, 6),
            'start_time' => sprintf('%02d:00:00', $startHour),
            'end_time' => sprintf('%02d:00:00', $endHour),
            'is_active' => true,
        ];
    }
}
