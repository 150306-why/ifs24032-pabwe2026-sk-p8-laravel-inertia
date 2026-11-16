<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.openai.key' => 'sk-test', 'blog.ai_max_articles' => 3]);
    }

    private function fakeOpenAi(array $articles): void
    {
        Http::fake(['api.openai.com/*' => Http::response([
            'choices' => [['message' => ['content' => json_encode(['articles' => $articles])]]],
        ])]);
    }

    public function test_generate_returns_results_without_saving_or_publishing(): void
    {
        $this->fakeOpenAi([['title' => 'Judul A', 'content' => '# A'], ['title' => 'Judul B', 'content' => '# B']]);
        $me = User::factory()->create();

        $this->actingAs($me)->post('/dashboard/posts/ai-generate', ['topic' => 'Tidur cukup', 'category' => 'Kesehatan', 'count' => 2])
            ->assertRedirect()
            ->assertSessionHas('ai_results', fn (array $r) => count($r) === 2 && $r[0]['category'] === 'Kesehatan' && $r[0]['title'] === 'Judul A');

        $this->assertSame(0, Post::count());
        Http::assertSent(fn (Request $r) => $r->hasHeader('Authorization', 'Bearer sk-test')
            && $r['model'] === config('services.openai.model')
            && str_contains($r['messages'][1]['content'], 'Tidur cukup'));
    }

    public function test_generate_validates_count_limit_and_fields(): void
    {
        $me = User::factory()->create();

        $this->actingAs($me)->post('/dashboard/posts/ai-generate', ['topic' => 'abc', 'category' => 'X', 'count' => 4])->assertSessionHasErrors('count');
        $this->actingAs($me)->post('/dashboard/posts/ai-generate', ['topic' => 'abc', 'category' => 'X', 'count' => 0])->assertSessionHasErrors('count');
        $this->actingAs($me)->post('/dashboard/posts/ai-generate', [])->assertSessionHasErrors(['topic', 'category', 'count']);
        Http::assertNothingSent();
    }

    public function test_generate_handles_failures_gracefully(): void
    {
        $me = User::factory()->create();
        $payload = ['topic' => 'Topik uji', 'category' => 'Opini', 'count' => 1];

        config(['services.openai.key' => null]);
        $this->actingAs($me)->post('/dashboard/posts/ai-generate', $payload)->assertSessionHas('error', 'API key OpenAI belum dikonfigurasi di server.');

        config(['services.openai.key' => 'sk-test']);
        Http::fake(['api.openai.com/*' => Http::sequence()->push([], 429)->push([], 500)->pushFailedConnection()]);

        $this->actingAs($me)->post('/dashboard/posts/ai-generate', $payload)->assertSessionHas('error', fn ($m) => str_contains($m, 'Batas penggunaan'));
        $this->actingAs($me)->post('/dashboard/posts/ai-generate', $payload)->assertSessionHas('error', fn ($m) => str_contains($m, '500'));
        $this->actingAs($me)->post('/dashboard/posts/ai-generate', $payload)->assertSessionHas('error', fn ($m) => str_contains($m, 'waktu permintaan habis'));

        $this->assertSame(0, Post::count());
    }

    public function test_save_stores_all_results_as_drafts(): void
    {
        $me = User::factory()->create();

        $this->actingAs($me)->post('/dashboard/posts/ai-save', ['articles' => [
            ['title' => 'AI Satu', 'category' => 'Teknologi', 'content' => 'isi 1'],
            ['title' => 'AI Dua', 'category' => 'Bisnis', 'content' => 'isi 2'],
        ]])->assertRedirect(route('dashboard.posts.index'))->assertSessionHas('success', '2 artikel AI disimpan sebagai draft.');

        $this->assertSame(2, $me->posts()->count());
        $this->assertSame(0, $me->posts()->where('is_published', true)->count());
        $this->assertNull($me->posts()->first()->published_at);
    }

    public function test_save_validates_payload(): void
    {
        $me = User::factory()->create();

        $this->actingAs($me)->post('/dashboard/posts/ai-save', [])->assertSessionHasErrors('articles');
        $this->actingAs($me)->post('/dashboard/posts/ai-save', ['articles' => [['title' => '', 'category' => '', 'content' => '']]])
            ->assertSessionHasErrors(['articles.0.title', 'articles.0.category', 'articles.0.content']);
        $this->actingAs($me)->post('/dashboard/posts/ai-save', ['articles' => array_fill(0, 4, ['title' => 'T', 'category' => 'C', 'content' => 'x'])])
            ->assertSessionHasErrors('articles');

        $this->assertSame(0, Post::count());
    }
}
