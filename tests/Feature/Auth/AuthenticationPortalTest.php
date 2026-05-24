<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_authentication_pages_render_successfully(): void
    {
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();
        $this->get(route('admin.login'))->assertOk();
    }

    public function test_customer_can_register_from_user_portal(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Pelanggan Baru',
            'email' => 'pelanggan@example.test',
            'phone_number' => '089900112233',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('landing'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'pelanggan@example.test',
            'role' => UserRole::Customer->value,
        ]);
    }

    public function test_admin_can_login_from_admin_portal(): void
    {
        $admin = User::factory()->admin()->create([
            'username' => 'admin_test',
            'email' => 'admin@example.test',
            'password' => 'password123',
        ]);

        $response = $this->post(route('admin.login.store'), [
            'login' => 'admin_test',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_customer_cannot_login_from_admin_portal(): void
    {
        User::factory()->customer()->create([
            'email' => 'customer@example.test',
            'password' => 'password123',
        ]);

        $this->post(route('admin.login.store'), [
            'login' => 'customer@example.test',
            'password' => 'password123',
        ])
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }
}
