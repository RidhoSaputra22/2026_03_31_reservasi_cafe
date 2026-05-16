<?php

namespace Database\Factories;

use App\Models\CafeProfile;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cafe_profile_id' => CafeProfile::factory(),
            'name' => fake()->words(2, true),
            'category' => fake()->randomElement(['Coffee', 'Non-Coffee', 'Food', 'Snack', 'Dessert']),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 18000, 85000),
            'is_available' => true,
        ];
    }
}
