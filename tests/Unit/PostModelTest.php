<?php

namespace Tests\Unit;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_relations_between_user_and_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $this->assertTrue($post->user->is($user));
        $this->assertTrue($user->posts->first()->is($post));
    }

    public function test_casts_and_hidden_attributes(): void
    {
        $post = Post::factory()->published()->create()->fresh();

        $this->assertTrue($post->is_published);
        $this->assertNotNull($post->published_at);
        $this->assertArrayNotHasKey('password', User::factory()->create()->toArray());
    }

    public function test_cover_url_accessor(): void
    {
        Storage::fake('public');

        $this->assertNull(Post::factory()->make()->cover_url);
        $this->assertStringContainsString('covers/a.jpg', Post::factory()->make(['cover_image' => 'covers/a.jpg'])->cover_url);
    }

    public function test_excerpt_strips_markdown_and_is_limited(): void
    {
        $post = Post::factory()->make(['content' => "# Judul\n\n**Tebal** dan *miring*\n\n- butir\n\n".str_repeat('kata ', 100)]);

        $this->assertStringNotContainsString('#', $post->excerpt);
        $this->assertStringNotContainsString('**', $post->excerpt);
        $this->assertLessThanOrEqual(163, mb_strlen($post->excerpt));
        $this->assertStringStartsWith('Judul Tebal dan miring butir', $post->excerpt);
    }

    public function test_scopes(): void
    {
        Post::factory()->published()->create(['title' => 'Satu', 'category' => 'A', 'content' => 'foo']);
        Post::factory()->draft()->create(['title' => 'Dua', 'category' => 'B', 'content' => 'bar']);

        $this->assertSame(1, Post::published()->count());
        $this->assertSame(2, Post::search(null)->count());
        $this->assertSame(2, Post::search('  ')->count());
        $this->assertSame(1, Post::search('FOO')->count());
        $this->assertSame(1, Post::category('B')->count());
        $this->assertSame(2, Post::category('')->count());
        $this->assertSame(1, Post::status('draft')->count());
        $this->assertSame(1, Post::status('published')->count());
        $this->assertSame(2, Post::status('nonsense')->count());
        $this->assertSame(1, Post::filter(['q' => 'satu', 'category' => 'A', 'status' => 'published'])->count());
        $this->assertSame(2, Post::filter([])->count());
    }

    public function test_listing_scope_truncates_content(): void
    {
        Post::factory()->create(['content' => str_repeat('x', 1000)]);

        $post = Post::listing()->first();

        $this->assertSame(400, mb_strlen($post->content));
    }
}
