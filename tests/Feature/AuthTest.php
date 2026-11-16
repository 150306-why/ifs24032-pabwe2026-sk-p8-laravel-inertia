<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_register_pages_render(): void
    {
        $this->get('/login')->assertOk()->assertInertia(fn (Assert $p) => $p->component('Auth/Login'));
        $this->get('/register')->assertOk()->assertInertia(fn (Assert $p) => $p->component('Auth/Register'));
    }

    public function test_login_form_uses_required_element_ids(): void
    {
        $source = file_get_contents(resource_path('js/Pages/Auth/Login.jsx'));

        foreach (['login-username', 'login-password', 'login-submit'] as $id) {
            $this->assertStringContainsString('id="'.$id.'"', $source);
        }
    }

    public function test_register_creates_member_and_logs_in(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi', 'username' => 'budi_01', 'email' => 'budi@example.com',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
            'role' => 'admin',
        ]);

        $user = User::where('username', 'budi_01')->firstOrFail();
        $response->assertRedirect(route('dashboard.posts.index'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame('member', $user->role);
        $this->assertNotSame('rahasia123', $user->password);
    }

    public function test_register_validates_input(): void
    {
        User::factory()->create(['username' => 'dupe', 'email' => 'dupe@example.com']);

        $this->post('/register', [
            'name' => '', 'username' => 'dupe', 'email' => 'dupe@example.com', 'password' => 'short',
        ])->assertSessionHasErrors(['name', 'username', 'email', 'password']);
    }

    public function test_member_and_admin_are_redirected_to_their_home(): void
    {
        $member = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->post('/login', ['username' => $member->username, 'password' => 'password'])
            ->assertRedirect(route('dashboard.posts.index'));
        auth()->logout();

        $this->post('/login', ['username' => $admin->username, 'password' => 'password'])
            ->assertRedirect(route('admin.users.index'));
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['username' => $user->username, 'password' => 'salah'])
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['username' => $user->username, 'password' => 'salah']);
        }

        $this->post('/login', ['username' => $user->username, 'password' => 'password'])
            ->assertSessionHasErrors('username');
        $this->assertStringContainsString('Terlalu banyak', session('errors')->first('username'));
        $this->assertGuest();
    }

    public function test_logout_ends_session(): void
    {
        $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login_and_authenticated_user_leaves_login_page(): void
    {
        $this->get('/dashboard/posts')->assertRedirect(route('login'));
        $this->get('/profile')->assertRedirect(route('login'));

        $user = User::factory()->create();
        $this->actingAs($user)->get('/login')->assertRedirect($user->homeUrl());
    }

    public function test_user_helpers(): void
    {
        $admin = User::factory()->admin()->make();
        $member = User::factory()->make();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($member->isAdmin());
        $this->assertSame(route('admin.users.index'), $admin->homeUrl());
        $this->assertSame(route('dashboard.posts.index'), $member->homeUrl());
    }

    public function test_role_is_not_mass_assignable(): void
    {
        $user = User::create(['name' => 'X', 'username' => 'xx1', 'email' => 'x@example.com', 'password' => 'password', 'role' => 'admin']);

        $this->assertSame('member', $user->fresh()->role);
    }
}
