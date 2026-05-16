<?php

namespace Database\Factories;

use App\Models\CafeProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CafeProfile>
 */
class CafeProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Cafe Amikospace',
            'description' => fake()->paragraph(),
            'address' => fake()->address(),
            'phone_number' => fake()->numerify('0411-######'),
            'opening_time' => '10:00:00',
            'closing_time' => '22:00:00',
            'facilities' => ['WiFi', 'AC', 'Stop Kontak', 'Area Indoor', 'Area Outdoor'],
            'reservation_rules' => 'Reservasi wajib melakukan down payment dan hadir minimal 15 menit sebelum slot berakhir.',
            'down_payment_amount' => fake()->randomFloat(2, 25000, 100000),
        ];
    }
}
