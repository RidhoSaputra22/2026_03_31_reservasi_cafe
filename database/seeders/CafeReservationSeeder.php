<?php

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use App\Enums\TableStatus;
use App\Models\CafeProfile;
use App\Models\CafeTable;
use App\Models\MenuItem;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\ReservationSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use RuntimeException;

class CafeReservationSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $profile = CafeProfile::factory()->create([
            'name' => 'Cafe Amiko',
            'description' => 'Creative coffee space yang hangat untuk kopi, musik, obrolan, dan pengalaman komunitas.',
            'address' => 'Jl. Meranti No.215, Paropo, Kec. Panakkukang, Kota Makassar, Sulawesi Selatan 90221',
            'phone_number' => '0411-889900',
            'opening_time' => '08:00:00',
            'closing_time' => '17:00:00',
            'facilities' => ['WiFi Cepat', 'AC', 'Stop Kontak', 'Area Indoor', 'Area Outdoor'],
            'reservation_rules' => 'Reservasi dilakukan sesuai slot yang aktif. Pembatalan sebaiknya diinformasikan secepat mungkin kepada tim.',
            'down_payment_amount' => 50000,
        ]);

        MenuItem::factory()->for($profile)->create([
            'name' => 'Espresso Amiko',
            'category' => 'Coffee',
            'price' => 22000,
        ]);

        MenuItem::factory()->for($profile)->create([
            'name' => 'Signature Matcha',
            'category' => 'Non-Coffee',
            'price' => 28000,
        ]);

        MenuItem::factory()->for($profile)->create([
            'name' => 'Chicken Rice Bowl',
            'category' => 'Food',
            'price' => 35000,
        ]);

        MenuItem::factory()->count(5)->for($profile)->create();

        $admin = User::factory()->admin()->create([
            'name' => 'Admin Amikospace',
            'username' => 'admin_amikospace',
            'email' => 'admin@amikospace.test',
            'phone_number' => '081111111111',
        ]);

        $staff = User::factory()->staff()->create([
            'name' => 'Staff Floor',
            'username' => 'staff_amikospace',
            'email' => 'staff@amikospace.test',
            'phone_number' => '082222222222',
        ]);

        $leadCustomer = User::factory()->customer()->create([
            'name' => 'Bima Pelanggan',
            'username' => 'bima_pelanggan',
            'email' => 'customer@amikospace.test',
            'phone_number' => '083333333333',
        ]);

        $customers = User::factory()->count(5)->customer()->create()->prepend($leadCustomer)->values();

        $tables = collect([
            ['code' => 'A1', 'name' => 'Meja A1', 'capacity' => 2, 'location' => 'Indoor'],
            ['code' => 'A2', 'name' => 'Meja A2', 'capacity' => 2, 'location' => 'Indoor'],
            ['code' => 'B1', 'name' => 'Meja B1', 'capacity' => 4, 'location' => 'Window Area'],
            ['code' => 'B2', 'name' => 'Meja B2', 'capacity' => 4, 'location' => 'Outdoor'],
            ['code' => 'C1', 'name' => 'Meja C1', 'capacity' => 6, 'location' => 'Indoor'],
            ['code' => 'C2', 'name' => 'Meja C2', 'capacity' => 8, 'location' => 'Outdoor'],
        ])->map(fn (array $attributes) => CafeTable::factory()->create($attributes))->values();

        $this->call(ReservationSlotSeeder::class);

        $slots = ReservationSlot::query()
            ->whereBetween('day_of_week', [1, 6])
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $nextOperationalDate = function (int $offset = 0): Carbon {
            $date = now()->copy()->startOfDay()->addDays($offset);

            while ($date->dayOfWeek === Carbon::SUNDAY) {
                $date->addDay();
            }

            return $date;
        };

        $previousOperationalDate = function (int $offset = 1): Carbon {
            $date = now()->copy()->startOfDay()->subDays($offset);

            while ($date->dayOfWeek === Carbon::SUNDAY) {
                $date->subDay();
            }

            return $date;
        };

        $resolveSchedule = function (Carbon $date, string $startTime, int $durationHours) use ($slots): array {
            $normalizedStartTime = Carbon::createFromFormat('H:i:s', $startTime)->format('H:i:s');
            $endTime = Carbon::createFromFormat('H:i:s', $normalizedStartTime)
                ->addHours($durationHours)
                ->format('H:i:s');

            $slot = $slots->first(
                fn (ReservationSlot $reservationSlot) => $reservationSlot->day_of_week === $date->dayOfWeek
                    && $reservationSlot->start_time <= $normalizedStartTime
                    && $reservationSlot->end_time >= $endTime
            );

            if (! $slot instanceof ReservationSlot) {
                throw new RuntimeException("Reservation slot {$normalizedStartTime}-{$endTime} for {$date->toDateString()} was not found.");
            }

            return [
                'slot' => $slot,
                'start_time' => $normalizedStartTime,
                'end_time' => $endTime,
                'duration_hours' => $durationHours,
            ];
        };

        $confirmedDate = $nextOperationalDate(1);
        $confirmedSchedule = $resolveSchedule($confirmedDate, '13:00:00', 2);

        $confirmedReservation = Reservation::factory()
            ->confirmed()
            ->for($customers[0])
            ->for($tables[0], 'cafeTable')
            ->for($confirmedSchedule['slot'], 'reservationSlot')
            ->create([
                'customer_name' => $customers[0]->name,
                'customer_phone' => $customers[0]->phone_number,
                'reservation_date' => $confirmedDate->toDateString(),
                'start_time' => $confirmedSchedule['start_time'],
                'end_time' => $confirmedSchedule['end_time'],
                'duration_hours' => $confirmedSchedule['duration_hours'],
                'guest_count' => 2,
                'notes' => 'Reservasi untuk meeting kecil.',
                'amount_due' => 50000,
                'confirmed_by' => $admin->id,
            ]);

        Payment::factory()
            ->paid()
            ->for($confirmedReservation)
            ->create([
                'type' => PaymentType::DownPayment->value,
                'amount' => 50000,
                'method' => PaymentMethod::Qris->value,
                'verified_by' => $admin->id,
            ]);

        $tables[0]->update(['status' => TableStatus::Reserved->value]);

        $reviewDate = $nextOperationalDate(2);
        $reviewSchedule = $resolveSchedule($reviewDate, '10:00:00', 3);

        $awaitingConfirmationReservation = Reservation::factory()
            ->awaitingConfirmation()
            ->for($customers[1])
            ->for($tables[2], 'cafeTable')
            ->for($reviewSchedule['slot'], 'reservationSlot')
            ->create([
                'customer_name' => $customers[1]->name,
                'customer_phone' => $customers[1]->phone_number,
                'reservation_date' => $reviewDate->toDateString(),
                'start_time' => $reviewSchedule['start_time'],
                'end_time' => $reviewSchedule['end_time'],
                'duration_hours' => $reviewSchedule['duration_hours'],
                'guest_count' => 4,
                'notes' => 'Ulang tahun kecil bersama teman.',
                'amount_due' => 50000,
            ]);

        Payment::factory()
            ->awaitingVerification()
            ->for($awaitingConfirmationReservation)
            ->create([
                'type' => PaymentType::DownPayment->value,
                'amount' => 50000,
                'method' => PaymentMethod::BankTransfer->value,
            ]);

        $tables[2]->update(['status' => TableStatus::Reserved->value]);

        $checkedInDate = now()->dayOfWeek === Carbon::SUNDAY
            ? $previousOperationalDate(1)
            : now()->copy()->startOfDay();
        $checkedInSchedule = $resolveSchedule($checkedInDate, '11:00:00', 2);

        $checkedInReservation = Reservation::factory()
            ->checkedIn()
            ->for($customers[2])
            ->for($tables[1], 'cafeTable')
            ->for($checkedInSchedule['slot'], 'reservationSlot')
            ->create([
                'customer_name' => $customers[2]->name,
                'customer_phone' => $customers[2]->phone_number,
                'reservation_date' => $checkedInDate->toDateString(),
                'start_time' => $checkedInSchedule['start_time'],
                'end_time' => $checkedInSchedule['end_time'],
                'duration_hours' => $checkedInSchedule['duration_hours'],
                'guest_count' => 2,
                'amount_due' => 75000,
                'confirmed_by' => $admin->id,
                'checked_in_by' => $staff->id,
                'confirmed_at' => $checkedInDate->copy()->setTime(10, 0),
                'checked_in_at' => $checkedInDate->copy()->setTime(11, 0),
            ]);

        Payment::factory()
            ->paid()
            ->for($checkedInReservation)
            ->create([
                'type' => PaymentType::DownPayment->value,
                'amount' => 75000,
                'method' => PaymentMethod::Cash->value,
                'verified_by' => $staff->id,
            ]);

        $tables[1]->update(['status' => TableStatus::Occupied->value]);

        $completedDate = $previousOperationalDate(1);
        $completedSchedule = $resolveSchedule($completedDate, '14:00:00', 2);

        $completedReservation = Reservation::factory()
            ->completed()
            ->for($customers[3])
            ->for($tables[3], 'cafeTable')
            ->for($completedSchedule['slot'], 'reservationSlot')
            ->create([
                'customer_name' => $customers[3]->name,
                'customer_phone' => $customers[3]->phone_number,
                'reservation_date' => $completedDate->toDateString(),
                'start_time' => $completedSchedule['start_time'],
                'end_time' => $completedSchedule['end_time'],
                'duration_hours' => $completedSchedule['duration_hours'],
                'guest_count' => 4,
                'amount_due' => 100000,
                'confirmed_by' => $admin->id,
                'checked_in_by' => $staff->id,
                'confirmed_at' => $completedDate->copy()->setTime(13, 0),
                'checked_in_at' => $completedDate->copy()->setTime(14, 0),
                'completed_at' => $completedDate->copy()->setTime(16, 0),
            ]);

        Payment::factory()
            ->paid()
            ->for($completedReservation)
            ->create([
                'type' => PaymentType::DownPayment->value,
                'amount' => 100000,
                'method' => PaymentMethod::Qris->value,
                'verified_by' => $admin->id,
            ]);

        $tables[3]->update(['status' => TableStatus::Completed->value]);

        $cancelledDate = $nextOperationalDate(3);
        $cancelledSchedule = $resolveSchedule($cancelledDate, '09:00:00', 1);

        Reservation::factory()
            ->cancelled()
            ->for($customers[4])
            ->for($tables[4], 'cafeTable')
            ->for($cancelledSchedule['slot'], 'reservationSlot')
            ->create([
                'customer_name' => $customers[4]->name,
                'customer_phone' => $customers[4]->phone_number,
                'reservation_date' => $cancelledDate->toDateString(),
                'start_time' => $cancelledSchedule['start_time'],
                'end_time' => $cancelledSchedule['end_time'],
                'duration_hours' => $cancelledSchedule['duration_hours'],
                'guest_count' => 5,
                'amount_due' => 0,
                'cancelled_by' => $customers[4]->id,
                'cancelled_at' => $cancelledDate->copy()->setTime(8, 30),
            ]);

        foreach ([5, 6, 7] as $offset) {
            $date = $nextOperationalDate($offset);
            $startTime = match ($offset) {
                5 => '08:00:00',
                6 => '12:00:00',
                default => '15:00:00',
            };
            $schedule = $resolveSchedule($date, $startTime, 1);
            $customer = $customers[($offset - 5) % $customers->count()];
            $table = $tables[($offset - 5) % $tables->count()];

            Reservation::factory()
                ->pendingPayment()
                ->for($customer)
                ->for($table, 'cafeTable')
                ->for($schedule['slot'], 'reservationSlot')
                ->create([
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone_number,
                    'reservation_date' => $date->toDateString(),
                    'start_time' => $schedule['start_time'],
                    'end_time' => $schedule['end_time'],
                    'duration_hours' => $schedule['duration_hours'],
                    'guest_count' => min($table->capacity, 2 + ($offset - 4)),
                    'amount_due' => 50000,
                ]);
        }
    }
}
