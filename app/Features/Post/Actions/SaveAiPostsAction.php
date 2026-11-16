<?php

namespace App\Features\Post\Actions;

use App\Models\User;

class SaveAiPostsAction
{
    public function __construct(private CreatePostAction $create)
    {
    }

    /** Semua artikel AI disimpan sebagai Draft; penulis yang memutuskan untuk memublikasikan. */
    public function execute(User $user, array $articles): int
    {
        foreach ($articles as $article) {
            $this->create->execute($user, [
                'title' => $article['title'],
                'category' => $article['category'],
                'content' => $article['content'],
                'is_published' => false,
            ]);
        }

        return count($articles);
    }
}
