<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_the_form_instead_of_literal_blade_directives(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Selamat datang kembali.')
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('data-password-toggle="#password"', false)
            ->assertDontSee("@yield('content')", false);
    }

    public function test_active_user_can_login_and_logout(): void
    {
        $user = User::factory()->create();

        $this->post('/cms/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('cms.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->get(route('cms.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('data-bs-target="#cmsSidebar"', false)
            ->assertDontSee("@yield('content')", false);

        $this->post(route('cms.logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->inactive()->create();
        $this->post('/cms/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_registration_route_does_not_exist_and_reset_link_can_be_requested(): void
    {
        $this->get('/register')->assertNotFound();
        Notification::fake();
        $user = User::factory()->create();
        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);
    }
}
