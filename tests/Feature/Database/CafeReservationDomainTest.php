<?php

namespace Tests\Feature\Database;

use App\Enums\ReservationStatus;
use App\Enums\TableStatus;
use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CafeReservationDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_builds_the_cafe_reservation_domain(): void
    {
        $this->seed();

        $this->assertDatabaseCount('cafe_profiles', 1);
        $this->assertDatabaseHas('users', [
            'email' => 'admin@amikospace.test',
            'role' => UserRole::Admin->value,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'staff@amikospace.test',
            'role' => UserRole::Staff->value,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'customer@amikospace.test',
            'role' => UserRole::Customer->value,
        ]);

        $reservation = Reservation::query()
            ->with(['user', 'cafeTable', 'reservationSlot', 'payments'])
            ->firstOrFail();

        $this->assertNotNull($reservation->user);
        $this->assertNotNull($reservation->cafeTable);
        $this->assertNotNull($reservation->reservationSlot);
        $this->assertNotEmpty($reservation->payments);
        $this->assertInstanceOf(ReservationStatus::class, $reservation->status);
        $this->assertContains($reservation->cafeTable->status, TableStatus::cases());

        $this->assertTrue(
            Payment::query()->whereHas('reservation')->exists(),
            'Pembayaran harus terhubung ke reservasi.'
        );
    }
}
