<?php

namespace App\Features\Post\Services;

use App\Features\Post\Exceptions\AiGenerationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OpenAIService
{
    /**
     * @return array<int, array{title: string, content: string, category: string}>
     *
     * @throws AiGenerationException
     */
    public function generateArticles(string $topic, string $category, int $count): array
    {
        $key = config('services.openai.key');

        if (blank($key)) {
            throw new AiGenerationException('API key OpenAI belum dikonfigurasi di server.');
        }

        try {
            $response = Http::withToken($key)
                ->baseUrl(config('services.openai.base_url'))
                ->timeout((int) config('services.openai.timeout'))
                ->acceptJson()
                ->post('/chat/completions', $this->payload($topic, $category, $count));
        } catch (ConnectionException) {
            throw new AiGenerationException('Tidak dapat terhubung ke OpenAI atau waktu permintaan habis. Silakan coba lagi.');
        }

        if ($response->status() === 429) {
            throw new AiGenerationException('Batas penggunaan OpenAI tercapai. Coba lagi beberapa saat lagi.');
        }

        if ($response->failed()) {
            throw new AiGenerationException('OpenAI gagal memproses permintaan (kode '.$response->status().').');
        }

        return $this->parse((string) data_get($response->json(), 'choices.0.message.content'), $category, $count);
    }

    private function payload(string $topic, string $category, int $count): array
    {
        return [
            'model' => config('services.openai.model'),
            'temperature' => 0.7,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Kamu adalah penulis blog profesional berbahasa Indonesia. Balas HANYA dengan JSON valid '
                        .'berbentuk {"articles":[{"title":"...","content":"..."}]}. Field "content" berisi artikel '
                        .'berformat Markdown (heading, paragraf, daftar) minimal 300 kata. Jangan menambahkan teks di luar JSON.',
                ],
                [
                    'role' => 'user',
                    'content' => "Tulis {$count} artikel berbeda untuk kategori \"{$category}\" dengan topik: {$topic}",
                ],
            ],
        ];
    }

    /** @return array<int, array{title: string, content: string, category: string}> */
    private function parse(string $raw, string $category, int $count): array
    {
        $decoded = json_decode($raw, true);
        $articles = is_array($decoded) ? ($decoded['articles'] ?? null) : null;

        if (! is_array($articles) || $articles === []) {
            throw new AiGenerationException('Respons OpenAI tidak valid. Silakan coba lagi.');
        }

        $result = [];

        foreach (array_slice($articles, 0, $count) as $article) {
            $title = is_array($article) ? ($article['title'] ?? null) : null;
            $content = is_array($article) ? ($article['content'] ?? null) : null;

            if (! is_string($title) || ! is_string($content) || trim($title) === '' || trim($content) === '') {
                throw new AiGenerationException('Respons OpenAI tidak lengkap. Silakan coba lagi.');
            }

            $result[] = [
                'title' => Str::limit(trim($title), 150, ''),
                'content' => trim($content),
                'category' => $category,
            ];
        }

        return $result;
    }
}
