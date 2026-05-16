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
        $startHour = fake()->randomElement([10, 13, 16, 18]);

        return [
            'name' => fake()->randomElement(['Brunch', 'Lunch', 'Afternoon', 'Dinner']),
            'day_of_week' => fake()->numberBetween(0, 6),
            'start_time' => sprintf('%02d:00:00', $startHour),
            'end_time' => sprintf('%02d:00:00', $startHour + 2),
            'is_active' => true,
        ];
    }
}
