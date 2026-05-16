<?php

namespace Database\Factories;

use App\Enums\TableStatus;
use App\Models\CafeTable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CafeTable>
 */
class CafeTableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('T-##'),
            'name' => 'Meja '.fake()->unique()->numberBetween(1, 99),
            'capacity' => fake()->numberBetween(2, 8),
            'status' => TableStatus::Available->value,
            'location' => fake()->randomElement(['Indoor', 'Outdoor', 'Window Area', 'Smoking Area']),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
