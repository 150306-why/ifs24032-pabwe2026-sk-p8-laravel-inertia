<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\HandlesSpreadsheets;
use Tests\TestCase;

class AdminMonitoringTest extends TestCase
{
    use HandlesSpreadsheets;
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_sees_all_users_with_article_counts_and_search(): void
    {
        $budi = User::factory()->create(['name' => 'Budi Santoso', 'username' => 'budi', 'email' => 'budi@example.com']);
        User::factory()->create(['name' => 'Citra']);
        Post::factory()->count(2)->for($budi)->create();

        $this->actingAs($this->admin)->get('/admin/users')->assertOk()->assertInertia(fn (Assert $p) => $p
            ->component('Admin/UserList')->has('users.data', 3)->where('users.total', 3));

        foreach (['santoso', 'BUDI', 'budi@example'] as $term) {
            $this->actingAs($this->admin)->get('/admin/users?q='.$term)->assertInertia(fn (Assert $p) => $p
                ->has('users.data', 1)->where('users.data.0.posts_count', 2));
        }
    }

    public function test_admin_sees_user_detail_with_their_posts(): void
    {
        $user = User::factory()->create();
        Post::factory()->published()->for($user)->create(['title' => 'Terbit']);
        Post::factory()->draft()->for($user)->create(['title' => 'Konsep']);
        Post::factory()->create();

        $this->actingAs($this->admin)->get("/admin/users/{$user->id}/posts")->assertOk()->assertInertia(fn (Assert $p) => $p
            ->component('Admin/UserPostList')
            ->where('user.id', $user->id)
            ->where('user.posts_count', 2)
            ->missing('user.password')
            ->has('posts.data', 2));

        $this->actingAs($this->admin)->get("/admin/users/{$user->id}/posts?status=draft")->assertInertia(fn (Assert $p) => $p->has('posts.data', 1)->where('posts.data.0.title', 'Konsep'));
        $this->actingAs($this->admin)->get('/admin/users/9999/posts')->assertNotFound();
    }

    public function test_admin_monitors_all_posts_including_drafts_with_filters(): void
    {
        Post::factory()->published()->create(['category' => 'Travel', 'title' => 'Jalan Jalan']);
        Post::factory()->draft()->create(['category' => 'Bisnis', 'title' => 'Rencana Bisnis']);

        $this->actingAs($this->admin)->get('/admin/posts')->assertOk()->assertInertia(fn (Assert $p) => $p
            ->component('Admin/PostList')->has('posts.data', 2)->has('posts.data.0.user.name')->has('allCategories', 2));

        $this->actingAs($this->admin)->get('/admin/posts?status=draft')->assertInertia(fn (Assert $p) => $p->has('posts.data', 1));
        $this->actingAs($this->admin)->get('/admin/posts?category=Travel')->assertInertia(fn (Assert $p) => $p->has('posts.data', 1)->where('posts.data.0.title', 'Jalan Jalan'));
        $this->actingAs($this->admin)->get('/admin/posts?q=rencana')->assertInertia(fn (Assert $p) => $p->has('posts.data', 1));
    }

    public function test_admin_exports_all_posts_with_author_column(): void
    {
        $a = User::factory()->create(['name' => 'Penulis A']);
        $b = User::factory()->create(['name' => 'Penulis B']);
        Post::factory()->for($a)->create();
        Post::factory()->for($b)->published()->create();

        $response = $this->actingAs($this->admin)->get('/admin/posts/export');

        $response->assertOk()->assertDownload('semua-artikel-'.now()->format('Ymd').'.xlsx');
        $rows = $this->readXlsx($response->streamedContent());
        $this->assertSame('Penulis', $rows[0][6]);
        $this->assertCount(3, $rows);
        $this->assertEqualsCanonicalizing(['Penulis A', 'Penulis B'], [$rows[1][6], $rows[2][6]]);
    }

    public function test_admin_exports_posts_of_specific_user(): void
    {
        $user = User::factory()->create(['name' => 'Target']);
        Post::factory()->count(2)->for($user)->create();
        Post::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/users/{$user->id}/posts/export");

        $response->assertOk()->assertDownload('artikel-'.$user->username.'-'.now()->format('Ymd').'.xlsx');
        $rows = $this->readXlsx($response->streamedContent());
        $this->assertCount(3, $rows);
        $this->assertSame(['Target', 'Target'], [$rows[1][6], $rows[2][6]]);
    }
}
