<?php

namespace Database\Seeders;

use App\Models\ReservationSlot;
use Illuminate\Database\Seeder;

class ReservationSlotSeeder extends Seeder
{
    /**
     * Seed the application's reservation slot windows.
     */
    public function run(): void
    {
        foreach (range(1, 6) as $dayOfWeek) {
            ReservationSlot::query()->updateOrCreate(
                [
                    'day_of_week' => $dayOfWeek,
                    'start_time' => '08:00:00',
                    'end_time' => '17:00:00',
                ],
                [
                    'name' => 'Jam Operasional',
                    'is_active' => true,
                ],
            );
        }
    }
}
