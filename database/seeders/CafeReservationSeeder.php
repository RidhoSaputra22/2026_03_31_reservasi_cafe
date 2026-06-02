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
            'opening_time' => '10:00:00',
            'closing_time' => '22:00:00',
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

        $slotTemplates = [
            ['name' => 'Brunch', 'start_time' => '10:00:00', 'end_time' => '12:00:00'],
            ['name' => 'Lunch', 'start_time' => '13:00:00', 'end_time' => '15:00:00'],
            ['name' => 'Dinner', 'start_time' => '18:00:00', 'end_time' => '20:00:00'],
        ];

        $slots = collect();

        foreach (range(0, 6) as $dayOfWeek) {
            foreach ($slotTemplates as $template) {
                $slots->push(ReservationSlot::factory()->create([
                    'name' => $template['name'],
                    'day_of_week' => $dayOfWeek,
                    'start_time' => $template['start_time'],
                    'end_time' => $template['end_time'],
                ]));
            }
        }

        $findSlot = function (Carbon $date, string $startTime) use ($slots): ReservationSlot {
            $slot = $slots->first(
                fn (ReservationSlot $reservationSlot) => $reservationSlot->day_of_week === $date->dayOfWeek
                    && $reservationSlot->start_time === $startTime
            );

            if (! $slot instanceof ReservationSlot) {
                throw new RuntimeException("Reservation slot {$startTime} for {$date->toDateString()} was not found.");
            }

            return $slot;
        };

        $confirmedDate = now()->addDay();
        $confirmedSlot = $findSlot($confirmedDate, '18:00:00');

        $confirmedReservation = Reservation::factory()
            ->confirmed()
            ->for($customers[0])
            ->for($tables[0], 'cafeTable')
            ->for($confirmedSlot, 'reservationSlot')
            ->create([
                'customer_name' => $customers[0]->name,
                'customer_phone' => $customers[0]->phone_number,
                'reservation_date' => $confirmedDate->toDateString(),
                'start_time' => $confirmedSlot->start_time,
                'end_time' => $confirmedSlot->end_time,
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

        $reviewDate = now()->addDays(2);
        $reviewSlot = $findSlot($reviewDate, '13:00:00');

        $awaitingConfirmationReservation = Reservation::factory()
            ->awaitingConfirmation()
            ->for($customers[1])
            ->for($tables[2], 'cafeTable')
            ->for($reviewSlot, 'reservationSlot')
            ->create([
                'customer_name' => $customers[1]->name,
                'customer_phone' => $customers[1]->phone_number,
                'reservation_date' => $reviewDate->toDateString(),
                'start_time' => $reviewSlot->start_time,
                'end_time' => $reviewSlot->end_time,
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

        $checkedInDate = now();
        $checkedInSlot = $findSlot($checkedInDate, '13:00:00');

        $checkedInReservation = Reservation::factory()
            ->checkedIn()
            ->for($customers[2])
            ->for($tables[1], 'cafeTable')
            ->for($checkedInSlot, 'reservationSlot')
            ->create([
                'customer_name' => $customers[2]->name,
                'customer_phone' => $customers[2]->phone_number,
                'reservation_date' => $checkedInDate->toDateString(),
                'start_time' => $checkedInSlot->start_time,
                'end_time' => $checkedInSlot->end_time,
                'guest_count' => 2,
                'amount_due' => 75000,
                'confirmed_by' => $admin->id,
                'checked_in_by' => $staff->id,
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

        $completedDate = now()->subDay();
        $completedSlot = $findSlot($completedDate, '18:00:00');

        $completedReservation = Reservation::factory()
            ->completed()
            ->for($customers[3])
            ->for($tables[3], 'cafeTable')
            ->for($completedSlot, 'reservationSlot')
            ->create([
                'customer_name' => $customers[3]->name,
                'customer_phone' => $customers[3]->phone_number,
                'reservation_date' => $completedDate->toDateString(),
                'start_time' => $completedSlot->start_time,
                'end_time' => $completedSlot->end_time,
                'guest_count' => 4,
                'amount_due' => 100000,
                'confirmed_by' => $admin->id,
                'checked_in_by' => $staff->id,
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

        $cancelledDate = now()->addDays(3);
        $cancelledSlot = $findSlot($cancelledDate, '10:00:00');

        Reservation::factory()
            ->cancelled()
            ->for($customers[4])
            ->for($tables[4], 'cafeTable')
            ->for($cancelledSlot, 'reservationSlot')
            ->create([
                'customer_name' => $customers[4]->name,
                'customer_phone' => $customers[4]->phone_number,
                'reservation_date' => $cancelledDate->toDateString(),
                'start_time' => $cancelledSlot->start_time,
                'end_time' => $cancelledSlot->end_time,
                'guest_count' => 5,
                'amount_due' => 0,
                'cancelled_by' => $customers[4]->id,
            ]);

        foreach ([5, 6, 7] as $offset) {
            $date = now()->addDays($offset);
            $slot = $findSlot($date, '10:00:00');
            $customer = $customers[($offset - 5) % $customers->count()];
            $table = $tables[($offset - 5) % $tables->count()];

            Reservation::factory()
                ->pendingPayment()
                ->for($customer)
                ->for($table, 'cafeTable')
                ->for($slot, 'reservationSlot')
                ->create([
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone_number,
                    'reservation_date' => $date->toDateString(),
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'guest_count' => min($table->capacity, 2 + ($offset - 4)),
                    'amount_due' => 50000,
                ]);
        }
    }
}
