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
            'admin.tables.index',
            'admin.slots.index',
            'admin.payments.index',
            'admin.profile.index',
            'admin.users.index',
        ] as $routeName) {
            $this->get(route($routeName))->assertOk();
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
}
