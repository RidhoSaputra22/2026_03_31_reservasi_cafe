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
use Illuminate\Support\Str;
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

        collect([
            ['name' => 'Espresso Amiko', 'category' => 'Coffee', 'price' => 22000],
            ['name' => 'Americano Gula Aren', 'category' => 'Coffee', 'price' => 25000],
            ['name' => 'Cappuccino', 'category' => 'Coffee', 'price' => 27000],
            ['name' => 'Signature Matcha', 'category' => 'Non-Coffee', 'price' => 28000],
            ['name' => 'Chocolate Creamy', 'category' => 'Non-Coffee', 'price' => 26000],
            ['name' => 'Lemon Tea', 'category' => 'Tea', 'price' => 18000],
            ['name' => 'Chicken Rice Bowl', 'category' => 'Food', 'price' => 35000],
            ['name' => 'Beef Teriyaki Bowl', 'category' => 'Food', 'price' => 42000],
            ['name' => 'French Fries', 'category' => 'Snack', 'price' => 24000],
            ['name' => 'Banana Nugget', 'category' => 'Snack', 'price' => 23000],
        ])->each(fn (array $menu) => MenuItem::factory()
            ->for($profile)
            ->create($menu));

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

        $customerSeeds = [
            ['name' => 'Andi Mahendra', 'email' => 'andi.mahendra@example.test', 'phone_number' => '081230000001'],
            ['name' => 'Siti Rahmawati', 'email' => 'siti.rahmawati@example.test', 'phone_number' => '081230000002'],
            ['name' => 'Muhammad Fadli', 'email' => 'muhammad.fadli@example.test', 'phone_number' => '081230000003'],
            ['name' => 'Nur Aisyah', 'email' => 'nur.aisyah@example.test', 'phone_number' => '081230000004'],
            ['name' => 'Dewi Anggraini', 'email' => 'dewi.anggraini@example.test', 'phone_number' => '081230000005'],
            ['name' => 'Rizky Pratama', 'email' => 'rizky.pratama@example.test', 'phone_number' => '081230000006'],
            ['name' => 'Putri Lestari', 'email' => 'putri.lestari@example.test', 'phone_number' => '081230000007'],
            ['name' => 'Fajar Nugroho', 'email' => 'fajar.nugroho@example.test', 'phone_number' => '081230000008'],
            ['name' => 'Intan Permata', 'email' => 'intan.permata@example.test', 'phone_number' => '081230000009'],
            ['name' => 'Agus Santoso', 'email' => 'agus.santoso@example.test', 'phone_number' => '081230000010'],
            ['name' => 'Maya Kartika', 'email' => 'maya.kartika@example.test', 'phone_number' => '081230000011'],
            ['name' => 'Dimas Saputra', 'email' => 'dimas.saputra@example.test', 'phone_number' => '081230000012'],
            ['name' => 'Rani Oktaviani', 'email' => 'rani.oktaviani@example.test', 'phone_number' => '081230000013'],
            ['name' => 'Yusuf Hidayat', 'email' => 'yusuf.hidayat@example.test', 'phone_number' => '081230000014'],
            ['name' => 'Nabila Zahra', 'email' => 'nabila.zahra@example.test', 'phone_number' => '081230000015'],
        ];

        $customers = collect($customerSeeds)
            ->map(fn (array $customer) => User::factory()->customer()->create([
                'name' => $customer['name'],
                'username' => Str::of($customer['name'])->lower()->slug('_')->toString(),
                'email' => $customer['email'],
                'phone_number' => $customer['phone_number'],
            ]))
            ->values();

        $tables = collect([
            ['code' => 'A1', 'name' => 'Meja A1', 'capacity' => 2, 'location' => 'Indoor'],
            ['code' => 'A2', 'name' => 'Meja A2', 'capacity' => 2, 'location' => 'Indoor'],
            ['code' => 'B1', 'name' => 'Meja B1', 'capacity' => 4, 'location' => 'Window Area'],
            ['code' => 'B2', 'name' => 'Meja B2', 'capacity' => 4, 'location' => 'Outdoor'],
            ['code' => 'C1', 'name' => 'Meja C1', 'capacity' => 6, 'location' => 'Indoor'],
            ['code' => 'C2', 'name' => 'Meja C2', 'capacity' => 8, 'location' => 'Outdoor'],
        ])
            ->map(fn (array $table) => CafeTable::factory()->create($table))
            ->keyBy('code');

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

        $todayOperationalDate = now()->dayOfWeek === Carbon::SUNDAY
            ? $previousOperationalDate(1)
            : now()->copy()->startOfDay();

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
                throw new RuntimeException(
                    "Reservation slot {$normalizedStartTime}-{$endTime} for {$date->toDateString()} was not found."
                );
            }

            return [
                'slot' => $slot,
                'start_time' => $normalizedStartTime,
                'end_time' => $endTime,
                'duration_hours' => $durationHours,
            ];
        };

        $reservationSeeds = [
            [
                'customer_index' => 0,
                'table_code' => 'B2',
                'date' => $previousOperationalDate(1),
                'start_time' => '14:00:00',
                'duration_hours' => 2,
                'guest_count' => 4,
                'state' => 'completed',
                'notes' => 'Reservasi makan siang keluarga.',
                'amount_due' => 100000,
                'payment' => [
                    'state' => 'paid',
                    'amount' => 100000,
                    'method' => PaymentMethod::Qris->value,
                    'verified_by' => 'admin',
                ],
            ],
            [
                'customer_index' => 1,
                'table_code' => 'A2',
                'date' => $todayOperationalDate,
                'start_time' => '11:00:00',
                'duration_hours' => 2,
                'guest_count' => 2,
                'state' => 'checkedIn',
                'notes' => 'Datang untuk kerja remote.',
                'amount_due' => 75000,
                'payment' => [
                    'state' => 'paid',
                    'amount' => 75000,
                    'method' => PaymentMethod::Cash->value,
                    'verified_by' => 'staff',
                ],
            ],
            [
                'customer_index' => 2,
                'table_code' => 'A1',
                'date' => $nextOperationalDate(1),
                'start_time' => '13:00:00',
                'duration_hours' => 2,
                'guest_count' => 2,
                'state' => 'confirmed',
                'notes' => 'Reservasi untuk meeting kecil.',
                'amount_due' => 50000,
                'payment' => [
                    'state' => 'paid',
                    'amount' => 50000,
                    'method' => PaymentMethod::Qris->value,
                    'verified_by' => 'admin',
                ],
            ],
            [
                'customer_index' => 3,
                'table_code' => 'B1',
                'date' => $nextOperationalDate(2),
                'start_time' => '10:00:00',
                'duration_hours' => 3,
                'guest_count' => 4,
                'state' => 'awaitingConfirmation',
                'notes' => 'Ulang tahun kecil bersama teman.',
                'amount_due' => 50000,
                'payment' => [
                    'state' => 'awaitingVerification',
                    'amount' => 50000,
                    'method' => PaymentMethod::BankTransfer->value,
                ],
            ],
            [
                'customer_index' => 4,
                'table_code' => 'C1',
                'date' => $nextOperationalDate(3),
                'start_time' => '09:00:00',
                'duration_hours' => 1,
                'guest_count' => 5,
                'state' => 'cancelled',
                'notes' => 'Dibatalkan karena jadwal berubah.',
                'amount_due' => 0,
                'payment' => null,
            ],
            [
                'customer_index' => 5,
                'table_code' => 'C2',
                'date' => $nextOperationalDate(4),
                'start_time' => '08:00:00',
                'duration_hours' => 1,
                'guest_count' => 6,
                'state' => 'pendingPayment',
                'notes' => 'Reservasi komunitas pagi.',
                'amount_due' => 50000,
                'payment' => null,
            ],
            [
                'customer_index' => 6,
                'table_code' => 'A1',
                'date' => $nextOperationalDate(5),
                'start_time' => '12:00:00',
                'duration_hours' => 1,
                'guest_count' => 2,
                'state' => 'pendingPayment',
                'notes' => 'Reservasi makan siang berdua.',
                'amount_due' => 50000,
                'payment' => null,
            ],
            [
                'customer_index' => 7,
                'table_code' => 'A2',
                'date' => $nextOperationalDate(6),
                'start_time' => '15:00:00',
                'duration_hours' => 1,
                'guest_count' => 2,
                'state' => 'pendingPayment',
                'notes' => 'Reservasi sore untuk diskusi singkat.',
                'amount_due' => 50000,
                'payment' => null,
            ],
            [
                'customer_index' => 8,
                'table_code' => 'C1',
                'date' => $nextOperationalDate(7),
                'start_time' => '10:00:00',
                'duration_hours' => 2,
                'guest_count' => 5,
                'state' => 'confirmed',
                'notes' => 'Reservasi untuk arisan kecil.',
                'amount_due' => 50000,
                'payment' => [
                    'state' => 'paid',
                    'amount' => 50000,
                    'method' => PaymentMethod::BankTransfer->value,
                    'verified_by' => 'admin',
                ],
            ],
            [
                'customer_index' => 9,
                'table_code' => 'C2',
                'date' => $nextOperationalDate(8),
                'start_time' => '13:00:00',
                'duration_hours' => 2,
                'guest_count' => 8,
                'state' => 'awaitingConfirmation',
                'notes' => 'Reservasi gathering tim kantor.',
                'amount_due' => 50000,
                'payment' => [
                    'state' => 'awaitingVerification',
                    'amount' => 50000,
                    'method' => PaymentMethod::BankTransfer->value,
                ],
            ],
            [
                'customer_index' => 10,
                'table_code' => 'A1',
                'date' => $previousOperationalDate(2),
                'start_time' => '08:00:00',
                'duration_hours' => 1,
                'guest_count' => 2,
                'state' => 'completed',
                'notes' => 'Sarapan dan kopi pagi.',
                'amount_due' => 50000,
                'payment' => [
                    'state' => 'paid',
                    'amount' => 50000,
                    'method' => PaymentMethod::Cash->value,
                    'verified_by' => 'staff',
                ],
            ],
            [
                'customer_index' => 11,
                'table_code' => 'C2',
                'date' => $previousOperationalDate(3),
                'start_time' => '10:00:00',
                'duration_hours' => 2,
                'guest_count' => 7,
                'state' => 'completed',
                'notes' => 'Rapat komunitas kreatif.',
                'amount_due' => 100000,
                'payment' => [
                    'state' => 'paid',
                    'amount' => 100000,
                    'method' => PaymentMethod::Qris->value,
                    'verified_by' => 'admin',
                ],
            ],
            [
                'customer_index' => 12,
                'table_code' => 'B1',
                'date' => $nextOperationalDate(9),
                'start_time' => '14:00:00',
                'duration_hours' => 2,
                'guest_count' => 4,
                'state' => 'confirmed',
                'notes' => 'Reservasi untuk keluarga kecil.',
                'amount_due' => 50000,
                'payment' => [
                    'state' => 'paid',
                    'amount' => 50000,
                    'method' => PaymentMethod::Qris->value,
                    'verified_by' => 'admin',
                ],
            ],
            [
                'customer_index' => 13,
                'table_code' => 'B2',
                'date' => $nextOperationalDate(10),
                'start_time' => '11:00:00',
                'duration_hours' => 1,
                'guest_count' => 4,
                'state' => 'cancelled',
                'notes' => 'Pelanggan membatalkan reservasi.',
                'amount_due' => 0,
                'payment' => null,
            ],
            [
                'customer_index' => 14,
                'table_code' => 'C1',
                'date' => $nextOperationalDate(11),
                'start_time' => '09:00:00',
                'duration_hours' => 1,
                'guest_count' => 3,
                'state' => 'pendingPayment',
                'notes' => 'Reservasi pagi, menunggu pembayaran DP.',
                'amount_due' => 50000,
                'payment' => null,
            ],
        ];

        $reservationStateHandlers = [
            'confirmed' => fn ($factory) => $factory->confirmed(),
            'awaitingConfirmation' => fn ($factory) => $factory->awaitingConfirmation(),
            'checkedIn' => fn ($factory) => $factory->checkedIn(),
            'completed' => fn ($factory) => $factory->completed(),
            'cancelled' => fn ($factory) => $factory->cancelled(),
            'pendingPayment' => fn ($factory) => $factory->pendingPayment(),
        ];

        $paymentStateHandlers = [
            'paid' => fn ($factory) => $factory->paid(),
            'awaitingVerification' => fn ($factory) => $factory->awaitingVerification(),
        ];

        collect($reservationSeeds)->each(function (array $seed) use (
            $admin,
            $staff,
            $customers,
            $tables,
            $resolveSchedule,
            $reservationStateHandlers,
            $paymentStateHandlers
        ): void {
            $customer = $customers[$seed['customer_index']];
            $table = $tables->get($seed['table_code']);

            if (! $table instanceof CafeTable) {
                throw new RuntimeException("Cafe table {$seed['table_code']} was not found.");
            }

            $date = $seed['date'];
            $schedule = $resolveSchedule($date, $seed['start_time'], $seed['duration_hours']);

            $startAt = Carbon::parse($date->toDateString().' '.$schedule['start_time']);
            $endAt = Carbon::parse($date->toDateString().' '.$schedule['end_time']);

            $statusAttributes = match ($seed['state']) {
                'confirmed' => [
                    'confirmed_by' => $admin->id,
                ],
                'checkedIn' => [
                    'confirmed_by' => $admin->id,
                    'checked_in_by' => $staff->id,
                    'confirmed_at' => $startAt->copy()->subHour(),
                    'checked_in_at' => $startAt->copy(),
                ],
                'completed' => [
                    'confirmed_by' => $admin->id,
                    'checked_in_by' => $staff->id,
                    'confirmed_at' => $startAt->copy()->subHour(),
                    'checked_in_at' => $startAt->copy(),
                    'completed_at' => $endAt->copy(),
                ],
                'cancelled' => [
                    'cancelled_by' => $customer->id,
                    'cancelled_at' => $startAt->copy()->subMinutes(30),
                ],
                default => [],
            };

            $reservationFactory = $reservationStateHandlers[$seed['state']](Reservation::factory());

            $reservation = $reservationFactory
                ->for($customer)
                ->for($table, 'cafeTable')
                ->for($schedule['slot'], 'reservationSlot')
                ->create(array_merge([
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone_number,
                    'reservation_date' => $date->toDateString(),
                    'start_time' => $schedule['start_time'],
                    'end_time' => $schedule['end_time'],
                    'duration_hours' => $schedule['duration_hours'],
                    'guest_count' => $seed['guest_count'],
                    'notes' => $seed['notes'],
                    'amount_due' => $seed['amount_due'],
                ], $statusAttributes));

            if ($seed['payment'] !== null) {
                $payment = $seed['payment'];

                $paymentFactory = $paymentStateHandlers[$payment['state']](Payment::factory());

                $paymentAttributes = [
                    'type' => PaymentType::DownPayment->value,
                    'amount' => $payment['amount'],
                    'method' => $payment['method'],
                ];

                if ($payment['state'] === 'paid') {
                    $paymentAttributes['verified_by'] = $payment['verified_by'] === 'staff'
                        ? $staff->id
                        : $admin->id;
                }

                $paymentFactory
                    ->for($reservation)
                    ->create($paymentAttributes);
            }
        });

        collect([
            'A1' => TableStatus::Reserved->value,
            'A2' => TableStatus::Occupied->value,
            'B1' => TableStatus::Reserved->value,
            'B2' => TableStatus::Completed->value,
        ])->each(function (string $status, string $tableCode) use ($tables): void {
            $tables->get($tableCode)?->update([
                'status' => $status,
            ]);
        });
    }
}
