<?php

namespace Tests\Feature\Admin;

use App\Enums\PaymentType;
use App\Models\CafeTable;
use App\Models\MenuItem;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\ReservationSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_panels_render_successfully(): void
    {
        $this->seed();
        $this->actingAs(User::query()->where('email', 'admin@amikospace.test')->firstOrFail());

        foreach ([
            'dashboard',
            'admin.reservations.index',
            'admin.menu.index',
            'admin.packages.index',
            'admin.tables.index',
            'admin.slots.index',
            'admin.payments.index',
            'admin.profile.index',
            'admin.users.index',
        ] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_admin_can_create_a_reservation_package_with_hourly_pricing(): void
    {
        $this->seed();
        $this->actingAs(User::query()->where('email', 'admin@amikospace.test')->firstOrFail());

        $response = $this->post(route('admin.packages.store'), [
            'name' => 'Paket Custom Malam',
            'category' => 'Custom Event',
            'image_path' => 'assets/images/hero.jpg',
            'summary' => 'Ringkasan paket custom malam.',
            'description' => 'Deskripsi lengkap paket custom malam.',
            'base_price' => 175000,
            'included_hours' => 2,
            'extra_hour_price' => 50000,
            'aliases_text' => 'paket-malam-custom',
            'facilities_text' => "Mocktail house\nArea indoor",
            'notes_text' => "Datang 15 menit lebih awal\nKonfirmasi dekor sebelumnya",
            'sort_order' => 7,
            'is_featured' => '1',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.packages.index'));

        $this->assertDatabaseHas('reservation_packages', [
            'name' => 'Paket Custom Malam',
            'category' => 'Custom Event',
            'base_price' => 175000,
            'included_hours' => 2,
            'extra_hour_price' => 50000,
            'is_featured' => true,
            'is_active' => true,
        ]);
    }

    public function test_admin_master_data_panels_show_edit_actions_and_prefilled_forms(): void
    {
        $this->seed();
        $this->actingAs(User::query()->where('email', 'admin@amikospace.test')->firstOrFail());

        $menuItem = MenuItem::factory()->create([
            'name' => '000 Edit Menu',
            'category' => 'Test Category',
        ]);

        $table = CafeTable::factory()->create([
            'code' => '000-EDIT',
            'name' => 'Meja Edit',
        ]);

        $slot = ReservationSlot::factory()->create([
            'name' => '000 Edit Slot',
            'day_of_week' => 0,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
        ]);

        $user = User::factory()->customer()->create([
            'name' => '000 Edit User',
            'username' => 'edit_user_panel',
            'email' => 'edit-user-panel@example.test',
        ]);

        $cases = [
            [
                'indexRoute' => route('admin.menu.index'),
                'editLink' => route('admin.menu.index', ['edit' => $menuItem->id]).'#form-menu',
                'editRoute' => route('admin.menu.index', ['edit' => $menuItem->id]),
                'updateRoute' => route('admin.menu.update', $menuItem),
                'expectedText' => ['Edit Menu', '000 Edit Menu', 'Batal Edit', 'Simpan Perubahan'],
            ],
            [
                'indexRoute' => route('admin.tables.index'),
                'editLink' => route('admin.tables.index', ['edit' => $table->id]).'#form-meja',
                'editRoute' => route('admin.tables.index', ['edit' => $table->id]),
                'updateRoute' => route('admin.tables.update', $table),
                'expectedText' => ['Edit Meja', '000-EDIT', 'Batal Edit', 'Simpan Perubahan'],
            ],
            [
                'indexRoute' => route('admin.slots.index'),
                'editLink' => route('admin.slots.index', ['edit' => $slot->id]).'#form-slot',
                'editRoute' => route('admin.slots.index', ['edit' => $slot->id]),
                'updateRoute' => route('admin.slots.update', $slot),
                'expectedText' => ['Edit Rentang', '000 Edit Slot', 'Batal Edit', 'Simpan Perubahan'],
            ],
            [
                'indexRoute' => route('admin.users.index'),
                'editLink' => route('admin.users.index', ['edit' => $user->id]).'#form-user',
                'editRoute' => route('admin.users.index', ['edit' => $user->id]),
                'updateRoute' => route('admin.users.update', $user),
                'expectedText' => ['Edit Pengguna', '000 Edit User', 'Batal Edit', 'Simpan Perubahan'],
            ],
        ];

        foreach ($cases as $case) {
            $this->get($case['indexRoute'])
                ->assertOk()
                ->assertSee($case['editLink'], false);

            $response = $this->get($case['editRoute']);

            $response->assertOk()
                ->assertSee($case['updateRoute'], false);

            foreach ($case['expectedText'] as $text) {
                $response->assertSee($text);
            }
        }
    }

    public function test_admin_global_search_returns_results(): void
    {
        $this->seed();
        $this->actingAs(User::query()->where('email', 'admin@amikospace.test')->firstOrFail());

        $this->getJson(route('admin.global-search', ['q' => 'RSV']))
            ->assertOk()
            ->assertJsonStructure([
                'results' => [
                    '*' => ['title', 'subtitle', 'category', 'icon', 'url'],
                ],
            ]);
    }

    public function test_admin_guest_is_redirected_to_admin_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_search_payments_by_reservation_code(): void
    {
        $this->seed();
        $this->actingAs(User::query()->where('email', 'admin@amikospace.test')->firstOrFail());

        $reservation = Reservation::factory()->create([
            'reservation_code' => 'RSV-SEARCH-QR',
            'customer_name' => 'Pelanggan QR',
        ]);

        Payment::factory()
            ->for($reservation)
            ->create([
                'payment_code' => 'PAY-SEARCH-QR',
            ]);

        $this->get(route('admin.payments.index', ['search' => 'RSV-SEARCH-QR']))
            ->assertOk()
            ->assertSee('PAY-SEARCH-QR')
            ->assertSee('RSV-SEARCH-QR');
    }

    public function test_admin_payments_page_includes_qr_scanner_modal(): void
    {
        $this->seed();
        $this->actingAs(User::query()->where('email', 'admin@amikospace.test')->firstOrFail());

        $this->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSee('Cari Pembayaran')
            ->assertSee('Cari Pembayaran via QR')
            ->assertSee('Hasil Scan')
            ->assertSee('Cari Sekarang');
    }

    public function test_admin_payments_page_shows_create_settlement_action_only_for_eligible_down_payment(): void
    {
        config([
            'services.midtrans.server_key' => 'SB-Mid-server-test',
        ]);

        Carbon::setTestNow(Carbon::create(2026, 6, 4, 10, 0, 0));

        $this->seed();
        $this->actingAs(User::query()->where('email', 'admin@amikospace.test')->firstOrFail());

        $eligibleReservation = Reservation::factory()->confirmed()->create([
            'reservation_code' => 'RSV-DP-ELIGIBLE',
            'reservation_date' => now()->addDay()->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'total_price' => 250000,
            'amount_due' => 200000,
        ]);

        $pastReservation = Reservation::factory()->confirmed()->create([
            'reservation_code' => 'RSV-DP-PAST',
            'reservation_date' => now()->subDay()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'total_price' => 250000,
            'amount_due' => 200000,
        ]);

        $failedDownPaymentReservation = Reservation::factory()->confirmed()->create([
            'reservation_code' => 'RSV-DP-FAILED',
            'reservation_date' => now()->addDay()->toDateString(),
            'start_time' => '18:00:00',
            'end_time' => '20:00:00',
            'total_price' => 250000,
            'amount_due' => 250000,
        ]);

        $hasSettlementReservation = Reservation::factory()->confirmed()->create([
            'reservation_code' => 'RSV-DP-SETTLED',
            'reservation_date' => now()->addDay()->toDateString(),
            'start_time' => '19:00:00',
            'end_time' => '21:00:00',
            'total_price' => 250000,
            'amount_due' => 200000,
        ]);

        Payment::factory()->paid()->for($eligibleReservation)->create([
            'payment_code' => 'PAY-DP-ELIGIBLE',
            'type' => PaymentType::DownPayment->value,
            'amount' => 50000,
        ]);

        Payment::factory()->paid()->for($pastReservation)->create([
            'payment_code' => 'PAY-DP-PAST',
            'type' => PaymentType::DownPayment->value,
            'amount' => 50000,
        ]);

        Payment::factory()->failed()->for($failedDownPaymentReservation)->create([
            'payment_code' => 'PAY-DP-FAILED',
            'type' => PaymentType::DownPayment->value,
            'amount' => 50000,
        ]);

        $sourcePayment = Payment::factory()->paid()->for($hasSettlementReservation)->create([
            'payment_code' => 'PAY-DP-SETTLED',
            'type' => PaymentType::DownPayment->value,
            'amount' => 50000,
        ]);

        Payment::factory()->failed()->for($hasSettlementReservation)->create([
            'payment_code' => 'PAY-SISA-SETTLED',
            'type' => PaymentType::FullPayment->value,
            'parent_payment_id' => $sourcePayment->id,
            'amount' => 200000,
        ]);

        $response = $this->get(route('admin.payments.index'));

        $response->assertOk()
            ->assertSee(route('admin.payments.settlement', $eligibleReservation), false)
            ->assertDontSee(route('admin.payments.settlement', $pastReservation), false)
            ->assertDontSee(route('admin.payments.settlement', $failedDownPaymentReservation), false)
            ->assertDontSee(route('admin.payments.settlement', $hasSettlementReservation), false);

        Carbon::setTestNow();
    }
}
