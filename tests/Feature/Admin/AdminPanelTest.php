<?php

namespace Tests\Feature\Admin;

use App\Models\User;
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
}
