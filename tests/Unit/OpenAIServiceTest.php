<?php

namespace Tests\Unit;

use App\Features\Post\Exceptions\AiGenerationException;
use App\Features\Post\Services\OpenAIService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenAIServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.openai.key' => 'sk-test']);
    }

    private function reply(mixed $content): void
    {
        Http::fake(['api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => is_string($content) ? $content : json_encode($content)]]]])]);
    }

    public function test_returns_normalized_articles_limited_to_requested_count(): void
    {
        $this->reply(['articles' => [
            ['title' => '  Satu  ', 'content' => ' Isi 1 '],
            ['title' => str_repeat('t', 200), 'content' => 'Isi 2'],
            ['title' => 'Tiga', 'content' => 'Isi 3'],
        ]]);

        $result = (new OpenAIService)->generateArticles('topik', 'Opini', 2);

        $this->assertCount(2, $result);
        $this->assertSame(['title' => 'Satu', 'content' => 'Isi 1', 'category' => 'Opini'], $result[0]);
        $this->assertSame(150, mb_strlen($result[1]['title']));
    }

    public function test_throws_when_key_missing(): void
    {
        config(['services.openai.key' => '']);

        $this->expectException(AiGenerationException::class);
        (new OpenAIService)->generateArticles('topik', 'A', 1);
    }

    public function test_throws_on_invalid_responses(): void
    {
        $invalid = ['bukan json', '{"articles": []}', '{"foo": 1}', '[]', json_encode(['articles' => ['teks']]),
            json_encode(['articles' => [['title' => 'T']]]), json_encode(['articles' => [['title' => ' ', 'content' => 'x']]]),
            json_encode(['articles' => [['title' => 1, 'content' => 'x']]])];

        foreach ($invalid as $content) {
            $this->reply($content);

            try {
                (new OpenAIService)->generateArticles('topik', 'A', 1);
                $this->fail('Seharusnya melempar exception untuk: '.$content);
            } catch (AiGenerationException $e) {
                $this->assertNotSame('', $e->getMessage());
            }
        }
    }

    public function test_throws_on_http_errors(): void
    {
        foreach ([429, 401, 500] as $status) {
            Http::fake(['api.openai.com/*' => Http::response(['error' => 'x'], $status)]);

            try {
                (new OpenAIService)->generateArticles('topik', 'A', 1);
                $this->fail("Seharusnya gagal untuk status {$status}");
            } catch (AiGenerationException $e) {
                $this->assertNotSame('', $e->getMessage());
            }
        }
    }
}
