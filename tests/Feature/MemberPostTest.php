<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MemberPostTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $override = []): array
    {
        return array_merge(['title' => 'Halo Dunia', 'category' => 'Teknologi', 'content' => '# Isi', 'is_published' => 0], $override);
    }

    public function test_dashboard_shows_stats_and_only_own_posts(): void
    {
        $me = User::factory()->create();
        Post::factory()->count(2)->published()->for($me)->create();
        Post::factory()->count(3)->draft()->for($me)->create();
        Post::factory()->count(4)->published()->create();

        $this->actingAs($me)->get('/dashboard/posts')->assertOk()->assertInertia(fn (Assert $p) => $p
            ->component('Post/Index')
            ->where('stats', ['total' => 5, 'published' => 2, 'draft' => 3])
            ->has('posts.data', 5));
    }

    public function test_dashboard_filters_by_status_and_search(): void
    {
        $me = User::factory()->create();
        Post::factory()->published()->for($me)->create(['title' => 'Alpha Satu']);
        Post::factory()->draft()->for($me)->create(['title' => 'Beta Dua']);

        $this->actingAs($me)->get('/dashboard/posts?status=draft')->assertInertia(fn (Assert $p) => $p->has('posts.data', 1)->where('posts.data.0.title', 'Beta Dua'));
        $this->actingAs($me)->get('/dashboard/posts?status=published')->assertInertia(fn (Assert $p) => $p->has('posts.data', 1));
        $this->actingAs($me)->get('/dashboard/posts?q=alpha')->assertInertia(fn (Assert $p) => $p->has('posts.data', 1)->where('posts.data.0.title', 'Alpha Satu'));
        $this->actingAs($me)->get('/dashboard/posts?status=bogus')->assertInertia(fn (Assert $p) => $p->has('posts.data', 2));
    }

    public function test_store_draft_and_published(): void
    {
        $me = User::factory()->create();

        $this->actingAs($me)->post('/dashboard/posts', $this->payload())
            ->assertRedirect(route('dashboard.posts.index'))
            ->assertSessionHas('success', 'Artikel berhasil disimpan sebagai draft.');

        $this->actingAs($me)->post('/dashboard/posts', $this->payload(['title' => 'Terbit', 'is_published' => 1]))
            ->assertSessionHas('success', 'Artikel berhasil dipublikasikan.');

        $draft = Post::where('title', 'Halo Dunia')->first();
        $this->assertFalse($draft->is_published);
        $this->assertNull($draft->published_at);
        $this->assertSame($me->id, $draft->user_id);
        $this->assertSame('halo-dunia', $draft->slug);

        $published = Post::where('title', 'Terbit')->first();
        $this->assertTrue($published->is_published);
        $this->assertNotNull($published->published_at);
    }

    public function test_user_id_from_request_is_ignored(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($me)->post('/dashboard/posts', $this->payload(['user_id' => $other->id]));

        $this->assertSame($me->id, Post::firstOrFail()->user_id);
    }

    public function test_duplicate_titles_get_unique_slugs(): void
    {
        $me = User::factory()->create();

        $this->actingAs($me)->post('/dashboard/posts', $this->payload(['title' => 'Sama']));
        $this->actingAs($me)->post('/dashboard/posts', $this->payload(['title' => 'Sama']));
        $this->actingAs($me)->post('/dashboard/posts', $this->payload(['title' => 'Sama']));

        $this->assertEqualsCanonicalizing(['sama', 'sama-2', 'sama-3'], Post::pluck('slug')->all());
    }

    public function test_store_validates_input(): void
    {
        $this->actingAs(User::factory()->create())->post('/dashboard/posts', [])
            ->assertSessionHasErrors(['title', 'category', 'content', 'is_published']);
    }

    public function test_store_with_cover_image(): void
    {
        Storage::fake('public');
        $me = User::factory()->create();

        $this->actingAs($me)->post('/dashboard/posts', $this->payload(['cover' => UploadedFile::fake()->image('cover.jpg', 600, 400)]));

        $post = Post::firstOrFail();
        $this->assertNotNull($post->cover_image);
        Storage::disk('public')->assertExists($post->cover_image);
        $this->assertNotNull($post->cover_url);
    }

    public function test_member_can_view_own_post_but_not_others(): void
    {
        $me = User::factory()->create();
        $mine = Post::factory()->for($me)->create();
        $theirs = Post::factory()->create();

        $this->actingAs($me)->get("/dashboard/posts/{$mine->id}")->assertOk()->assertInertia(fn (Assert $p) => $p
            ->component('Post/Show')->where('post.id', $mine->id)->where('post.user.id', $me->id));
        $this->actingAs($me)->get("/dashboard/posts/{$theirs->id}")->assertForbidden();
        $this->actingAs($me)->get('/dashboard/posts/9999')->assertNotFound();
    }

    public function test_update_changes_data_and_slug(): void
    {
        $me = User::factory()->create();
        $post = Post::factory()->draft()->for($me)->create(['title' => 'Lama', 'slug' => 'lama']);

        $this->actingAs($me)->put("/dashboard/posts/{$post->id}", $this->payload(['title' => 'Judul Baru', 'is_published' => 1]))
            ->assertRedirect()->assertSessionHas('success');

        $post->refresh();
        $this->assertSame('Judul Baru', $post->title);
        $this->assertSame('judul-baru', $post->slug);
        $this->assertTrue($post->is_published);
        $this->assertNotNull($post->published_at);
    }

    public function test_update_keeps_slug_and_published_at_when_unchanged(): void
    {
        $me = User::factory()->create();
        $at = now()->subDays(3)->startOfSecond();
        $post = Post::factory()->for($me)->create(['title' => 'Tetap', 'slug' => 'tetap', 'is_published' => true, 'published_at' => $at]);

        $this->actingAs($me)->put("/dashboard/posts/{$post->id}", $this->payload(['title' => 'Tetap', 'is_published' => 1, 'content' => 'baru']));

        $post->refresh();
        $this->assertSame('tetap', $post->slug);
        $this->assertTrue($post->published_at->equalTo($at));
    }

    public function test_published_post_can_become_draft(): void
    {
        $me = User::factory()->create();
        $post = Post::factory()->published()->for($me)->create();

        $this->actingAs($me)->put("/dashboard/posts/{$post->id}", $this->payload(['title' => $post->title, 'is_published' => 0]));

        $this->assertFalse($post->refresh()->is_published);
        $this->assertNull($post->published_at);
    }

    public function test_update_with_new_cover_replaces_old_file(): void
    {
        Storage::fake('public');
        $me = User::factory()->create();
        Storage::disk('public')->put('covers/old.jpg', 'x');
        $post = Post::factory()->for($me)->create(['cover_image' => 'covers/old.jpg']);

        $this->actingAs($me)->put("/dashboard/posts/{$post->id}", $this->payload(['title' => $post->title, 'cover' => UploadedFile::fake()->image('new.png')]));

        Storage::disk('public')->assertMissing('covers/old.jpg');
        Storage::disk('public')->assertExists($post->refresh()->cover_image);
    }

    public function test_member_cannot_update_or_delete_others_posts(): void
    {
        $me = User::factory()->create();
        $post = Post::factory()->create(['title' => 'Asli']);

        $this->actingAs($me)->put("/dashboard/posts/{$post->id}", $this->payload())->assertForbidden();
        $this->actingAs($me)->patch("/dashboard/posts/{$post->id}/publish")->assertForbidden();
        $this->actingAs($me)->delete("/dashboard/posts/{$post->id}")->assertForbidden();

        $this->assertSame('Asli', $post->fresh()->title);
    }

    public function test_delete_removes_post_and_cover(): void
    {
        Storage::fake('public');
        $me = User::factory()->create();
        Storage::disk('public')->put('covers/a.jpg', 'x');
        $post = Post::factory()->for($me)->create(['cover_image' => 'covers/a.jpg']);
        $plain = Post::factory()->for($me)->create();

        $this->actingAs($me)->delete("/dashboard/posts/{$post->id}")->assertRedirect(route('dashboard.posts.index'));
        $this->actingAs($me)->delete("/dashboard/posts/{$plain->id}")->assertSessionHas('success');

        $this->assertModelMissing($post);
        $this->assertModelMissing($plain);
        Storage::disk('public')->assertMissing('covers/a.jpg');
    }

    public function test_publish_toggle_both_directions(): void
    {
        $me = User::factory()->create();
        $post = Post::factory()->draft()->for($me)->create();

        $this->actingAs($me)->patch("/dashboard/posts/{$post->id}/publish")->assertSessionHas('success', 'Artikel dipublikasikan.');
        $this->assertTrue($post->refresh()->is_published);
        $this->assertNotNull($post->published_at);

        $this->actingAs($me)->patch("/dashboard/posts/{$post->id}/publish")->assertSessionHas('success', 'Artikel dikembalikan menjadi draft.');
        $this->assertFalse($post->refresh()->is_published);
        $this->assertNull($post->published_at);
    }

    public function test_deleting_user_cascades_posts(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(2)->for($user)->create();

        $user->delete();

        $this->assertSame(0, Post::count());
    }
}
