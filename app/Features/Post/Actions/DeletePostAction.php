<?php

namespace App\Features\Post\Actions;

use App\Features\Post\Models\Post;
use App\Features\Post\Services\CoverImageService;

class DeletePostAction
{
    public function __construct(private CoverImageService $covers)
    {
    }

    public function execute(Post $post): void
    {
        $this->covers->delete($post->cover_image);

        $post->delete();
    }
}
