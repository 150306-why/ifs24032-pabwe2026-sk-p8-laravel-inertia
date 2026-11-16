<?php

namespace App\Features\Post\Actions;

use App\Features\Post\Services\OpenAIService;

class GeneratePostsAction
{
    public function __construct(private OpenAIService $openai)
    {
    }

    /**
     * Hasil AI hanya dikembalikan untuk diperiksa penulis; belum disimpan ke database.
     *
     * @return array<int, array{title: string, content: string, category: string}>
     */
    public function execute(string $topic, string $category, int $count): array
    {
        return $this->openai->generateArticles(trim($topic), trim($category), $count);
    }
}
