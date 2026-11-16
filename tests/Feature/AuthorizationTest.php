<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_access_admin_routes(): void
    {
        $member = User::factory()->create();

        foreach (['/admin/users', '/admin/posts', '/admin/posts/export', "/admin/users/{$member->id}/posts", "/admin/users/{$member->id}/posts/export"] as $url) {
            $this->actingAs($member)->get($url)->assertForbidden();
        }
    }

    public function test_admin_cannot_use_member_dashboard(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->create();

        $this->actingAs($admin)->get('/dashboard/posts')->assertForbidden();
        $this->actingAs($admin)->put("/dashboard/posts/{$post->id}", [])->assertForbidden();
        $this->actingAs($admin)->delete("/dashboard/posts/{$post->id}")->assertForbidden();
    }

    public function test_admin_has_no_edit_or_delete_routes_for_monitoring(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->create();

        $this->actingAs($admin)->put('/admin/posts', [])->assertStatus(405);
        $this->actingAs($admin)->delete("/admin/posts/{$post->id}")->assertNotFound();
        $this->assertModelExists($post);
    }

    public function test_dashboard_root_renders_posts_page_without_redirect(): void
    {
        $me = User::factory()->create();
        Post::factory()->for($me)->create();

        $this->actingAs($me)->get('/dashboard')->assertOk()->assertInertia(fn (Assert $p) => $p
            ->component('Post/Index')
            ->has('posts.data', 1)
            ->has('stats')
            ->has('filters'));

        $this->actingAs(User::factory()->admin()->create())->get('/dashboard')->assertForbidden();
    }

    public function test_guest_is_sent_to_login_from_dashboard_root(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_shared_props_expose_user_without_secrets(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/')->assertInertia(fn (Assert $p) => $p
            ->where('auth.user.role', 'admin')
            ->where('auth.user.home', $admin->homeUrl())
            ->missing('auth.user.password')
            ->has('categories')
            ->where('ai.max', config('blog.ai_max_articles')));

        auth()->logout();
        $this->get('/')->assertInertia(fn (Assert $p) => $p->where('auth.user', null));
    }

    public function test_front_end_requirements(): void
    {
        $public = file_get_contents(resource_path('js/Layouts/PublicLayout.jsx'));
        $this->assertStringContainsString('href="/login"', $public);
        $this->assertStringContainsString('href="/register"', $public);
        $this->assertStringContainsString('sticky top-0', $public);

        $modal = file_get_contents(resource_path('js/Components/FullScreenModal.jsx'));
        $this->assertStringContainsString('fixed inset-0', $modal);
        $this->assertStringContainsString('h-screen w-screen', $modal);

        $form = file_get_contents(resource_path('js/Components/PostFormModal.jsx'));
        $this->assertStringContainsString('<FullScreenModal', $form);
        $this->assertStringContainsString('Memproses tindakan...', file_get_contents(resource_path('js/Components/ProcessingPill.jsx')));
    }

    public function test_storage_directories_are_kept_in_git(): void
    {
        $dirs = ['app', 'app/private', 'app/public', 'framework', 'framework/cache', 'framework/cache/data', 'framework/sessions', 'framework/testing', 'framework/views', 'logs'];

        foreach ($dirs as $dir) {
            $file = base_path("storage/{$dir}/.gitignore");
            $this->assertFileExists($file, "storage/{$dir}/.gitignore tidak ada");
            $lines = array_map('trim', file($file));
            $this->assertContains('*', $lines);
            $this->assertContains('!.gitignore', $lines);
        }
    }
}
