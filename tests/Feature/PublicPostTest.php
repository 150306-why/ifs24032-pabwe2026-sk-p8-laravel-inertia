<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_lists_latest_featured_and_categories(): void
    {
        Post::factory()->published()->create(['category' => 'Bisnis']);
        $withCover = Post::factory()->published()->create(['category' => 'Bisnis', 'cover_image' => 'covers/a.jpg']);
        Post::factory()->draft()->create(['category' => 'Rahasia']);

        $this->get('/')->assertOk()->assertInertia(fn (Assert $p) => $p
            ->component('Public/Home')
            ->has('latest', 2)
            ->where('featured.0.id', $withCover->id)
            ->has('categories', 1)
            ->where('categories.0.category', 'Bisnis')
            ->where('categories.0.total', 2));
    }

    public function test_article_list_shows_only_published_with_pagination(): void
    {
        Post::factory()->count(11)->published()->create();
        Post::factory()->count(2)->draft()->create();

        $this->get('/articles')->assertOk()->assertInertia(fn (Assert $p) => $p
            ->component('Public/PostIndex')
            ->has('posts.data', 9)
            ->where('posts.total', 11)
            ->has('publicCategories'));

        $this->get('/articles?page=2')->assertInertia(fn (Assert $p) => $p->has('posts.data', 2));
    }

    public function test_search_by_title_and_content(): void
    {
        Post::factory()->published()->create(['title' => 'Belajar Laravel Dasar', 'content' => 'isi biasa']);
        Post::factory()->published()->create(['title' => 'Resep Nasi', 'content' => 'rahasia kata-unik-xyz']);
        Post::factory()->draft()->create(['title' => 'Laravel Draft']);

        $this->get('/articles?q=LARAVEL')->assertInertia(fn (Assert $p) => $p->has('posts.data', 1)->where('posts.data.0.title', 'Belajar Laravel Dasar'));
        $this->get('/articles?q=kata-unik-xyz')->assertInertia(fn (Assert $p) => $p->has('posts.data', 1)->where('posts.data.0.title', 'Resep Nasi'));
    }

    public function test_search_treats_wildcards_literally_and_ignores_array_input(): void
    {
        Post::factory()->count(2)->published()->create();

        $this->get('/articles?q=%25')->assertInertia(fn (Assert $p) => $p->has('posts.data', 0));
        $this->get('/articles?q=_')->assertInertia(fn (Assert $p) => $p->has('posts.data', 0));
        $this->get('/articles?q[]=x')->assertOk()->assertInertia(fn (Assert $p) => $p->has('posts.data', 2)->where('filters.q', ''));
    }

    public function test_filter_by_category(): void
    {
        Post::factory()->count(2)->published()->create(['category' => 'Travel']);
        Post::factory()->published()->create(['category' => 'Opini']);

        $this->get('/articles?category=Travel')->assertInertia(fn (Assert $p) => $p->has('posts.data', 2)->where('filters.category', 'Travel'));
    }

    public function test_published_article_detail_includes_author_and_related(): void
    {
        $author = User::factory()->create(['name' => 'Siti']);
        $post = Post::factory()->for($author)->published()->create(['category' => 'Teknologi']);
        Post::factory()->published()->create(['category' => 'Teknologi']);
        Post::factory()->published()->create(['category' => 'Lain']);

        $this->get("/articles/{$post->slug}")->assertOk()->assertInertia(fn (Assert $p) => $p
            ->component('Public/PostShow')
            ->where('post.title', $post->title)
            ->where('post.user.name', 'Siti')
            ->has('post.content')
            ->has('related', 1));
    }

    public function test_draft_article_is_not_accessible_publicly(): void
    {
        $draft = Post::factory()->draft()->create();

        $this->get("/articles/{$draft->slug}")->assertNotFound();
        $this->get('/articles/tidak-ada')->assertNotFound();
    }
}
