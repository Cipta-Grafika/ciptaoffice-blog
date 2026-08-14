<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Enums\UserRole;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $user = User::factory()->create();
        Post::create([
            'author_id' => $user->id,
            'title' => 'Catatan Profil',
            'slug' => 'catatan-profil',
            'excerpt' => 'Ringkasan artikel profil.',
            'body_html' => '<p>Isi artikel.</p>',
            'status' => PostStatus::Draft,
        ]);

        $this->actingAs($user)->get(route('cms.profile.edit'))
            ->assertOk()
            ->assertSee('Profil saya')
            ->assertSee($user->name)
            ->assertSee($user->email)
            ->assertSee($user->role->label())
            ->assertSee('Simpan profil')
            ->assertSee(route('cms.profile.password.update'), false)
            ->assertSee('name="username"', false)
            ->assertSee('autocomplete="username"', false)
            ->assertSee('name="current_password"', false)
            ->assertSee('name="password_confirmation"', false)
            ->assertSee('data-password-toggle="#new_password"', false)
            ->assertDontSee('Ganti kata sandi');
    }

    public function test_guest_cannot_access_profile_page(): void
    {
        $this->get(route('cms.profile.edit'))->assertRedirect(route('login'));
    }

    public function test_user_can_update_own_identity_without_changing_access_level(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Author,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)->put(route('cms.profile.update'), [
            'name' => 'Editor CiptaOffice',
            'email' => 'editor@ciptaoffice.test',
            'role' => UserRole::Admin->value,
            'is_active' => false,
        ])->assertRedirect()->assertSessionHas('success', 'Profil berhasil diperbarui.');

        $user->refresh();
        $this->assertSame('Editor CiptaOffice', $user->name);
        $this->assertSame('editor@ciptaoffice.test', $user->email);
        $this->assertNull($user->email_verified_at);
        $this->assertSame(UserRole::Author, $user->role);
        $this->assertTrue($user->is_active);
    }

    public function test_profile_email_must_be_unique_except_for_current_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->put(route('cms.profile.update'), [
            'name' => $user->name,
            'email' => $other->email,
        ])->assertSessionHasErrors('email');

        $this->actingAs($user)->put(route('cms.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
        ])->assertSessionHasNoErrors();
    }

    public function test_user_can_update_password_from_profile(): void
    {
        $user = User::factory()->create(['password' => 'password-lama']);

        $this->actingAs($user)->put(route('cms.profile.password.update'), [
            'current_password' => 'password-lama',
            'password' => 'password-baru-aman',
            'password_confirmation' => 'password-baru-aman',
        ])->assertRedirect(route('cms.profile.edit'))
            ->assertSessionHas('success', 'Kata sandi berhasil diperbarui.');

        $this->assertTrue(Hash::check('password-baru-aman', $user->fresh()->password));
    }

    public function test_password_update_requires_current_password_and_confirmation(): void
    {
        $user = User::factory()->create(['password' => 'password-lama']);

        $this->actingAs($user)->from(route('cms.profile.edit'))->put(route('cms.profile.password.update'), [
            'current_password' => 'keliru',
            'password' => 'password-baru-aman',
            'password_confirmation' => 'tidak-sama',
        ])->assertRedirect(route('cms.profile.edit'))
            ->assertSessionHasErrors(['current_password', 'password']);

        $this->assertTrue(Hash::check('password-lama', $user->fresh()->password));
    }

    public function test_legacy_password_page_redirects_to_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('cms.password.edit'))
            ->assertRedirect(route('cms.profile.edit'));
    }
}
