<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CoverTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_cover_and_replace_old(): void
    {
        Storage::fake('public');
        $me = User::factory()->create();
        $post = Post::factory()->for($me)->create();

        $this->actingAs($me)->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->image('a.jpg')])->assertSessionHas('success');
        $first = $post->refresh()->cover_image;
        Storage::disk('public')->assertExists($first);

        $this->actingAs($me)->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->image('b.png')]);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($post->refresh()->cover_image);
    }

    public function test_delete_cover(): void
    {
        Storage::fake('public');
        $me = User::factory()->create();
        Storage::disk('public')->put('covers/x.jpg', 'x');
        $post = Post::factory()->for($me)->create(['cover_image' => 'covers/x.jpg']);

        $this->actingAs($me)->delete("/dashboard/posts/{$post->id}/cover")->assertSessionHas('success');

        $this->assertNull($post->refresh()->cover_image);
        Storage::disk('public')->assertMissing('covers/x.jpg');
    }

    public function test_cover_validation_rejects_wrong_type_and_large_file(): void
    {
        Storage::fake('public');
        $me = User::factory()->create();
        $post = Post::factory()->for($me)->create();

        $this->actingAs($me)->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf')])->assertSessionHasErrors('cover');
        $this->actingAs($me)->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->create('script.svg', 10, 'image/svg+xml')])->assertSessionHasErrors('cover');
        $this->actingAs($me)->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->image('big.jpg')->size(3000)])->assertSessionHasErrors('cover');
        $this->actingAs($me)->post("/dashboard/posts/{$post->id}/cover", [])->assertSessionHasErrors('cover');

        $this->assertNull($post->refresh()->cover_image);
    }

    public function test_cannot_change_cover_of_others_post(): void
    {
        Storage::fake('public');
        $me = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($me)->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->image('a.jpg')])->assertForbidden();
        $this->actingAs($me)->delete("/dashboard/posts/{$post->id}/cover")->assertForbidden();
    }
}
