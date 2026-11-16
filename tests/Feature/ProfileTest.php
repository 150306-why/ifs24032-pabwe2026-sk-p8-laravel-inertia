<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_renders_for_member_and_admin(): void
    {
        foreach ([User::factory()->create(), User::factory()->admin()->create()] as $user) {
            $this->actingAs($user)->get('/profile')->assertOk()->assertInertia(fn (Assert $p) => $p
                ->component('Profile/Edit')->where('profile.username', $user->username)->missing('profile.password'));
        }
    }

    public function test_update_profile_without_password(): void
    {
        $user = User::factory()->create();
        $hash = $user->password;

        $this->actingAs($user)->put('/profile', ['name' => 'Nama Baru', 'username' => 'baru_01', 'email' => 'baru@example.com'])
            ->assertRedirect(route('profile.edit'))->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('baru_01', $user->username);
        $this->assertSame($hash, $user->password);
    }

    public function test_update_password_requires_current_password(): void
    {
        $user = User::factory()->create();
        $data = ['name' => $user->name, 'username' => $user->username, 'email' => $user->email, 'password' => 'passwordbaru', 'password_confirmation' => 'passwordbaru'];

        $this->actingAs($user)->put('/profile', $data)->assertSessionHasErrors('current_password');
        $this->actingAs($user)->put('/profile', $data + ['current_password' => 'salah'])->assertSessionHasErrors('current_password');
        $this->actingAs($user)->put('/profile', $data + ['current_password' => 'password'])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('passwordbaru', $user->refresh()->password));
    }

    public function test_update_rejects_duplicates_of_other_users_but_allows_own_values(): void
    {
        $other = User::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->put('/profile', ['name' => 'X', 'username' => $other->username, 'email' => $other->email])
            ->assertSessionHasErrors(['username', 'email']);
        $this->actingAs($user)->put('/profile', ['name' => 'X', 'username' => $user->username, 'email' => $user->email])
            ->assertSessionHasNoErrors();
    }

    public function test_role_cannot_be_changed_through_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/profile', ['name' => 'X', 'username' => $user->username, 'email' => $user->email, 'role' => 'admin']);

        $this->assertSame('member', $user->refresh()->role);
    }
}
