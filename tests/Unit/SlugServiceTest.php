<?php

namespace Tests\Unit;

use App\Features\Post\Models\Post;
use App\Features\Post\Services\SlugService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlugServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_slug_and_increments_on_collision(): void
    {
        $service = new SlugService;

        $this->assertSame('halo-dunia', $service->unique('Halo Dunia!'));

        Post::factory()->create(['slug' => 'halo-dunia']);
        $this->assertSame('halo-dunia-2', $service->unique('Halo Dunia'));

        Post::factory()->create(['slug' => 'halo-dunia-2']);
        $this->assertSame('halo-dunia-3', $service->unique('Halo Dunia'));
    }

    public function test_ignores_the_current_post_and_falls_back_for_empty_slug(): void
    {
        $post = Post::factory()->create(['slug' => 'milik-sendiri']);
        $service = new SlugService;

        $this->assertSame('milik-sendiri', $service->unique('Milik Sendiri', $post->id));
        $this->assertSame('artikel', $service->unique('!!!'));
    }
}
