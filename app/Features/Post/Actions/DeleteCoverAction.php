<?php

namespace App\Features\Post\Actions;

use App\Features\Post\Models\Post;
use App\Features\Post\Services\CoverImageService;

class DeleteCoverAction
{
    public function __construct(private CoverImageService $covers)
    {
    }

    public function execute(Post $post): Post
    {
        $this->covers->delete($post->cover_image);

        $post->cover_image = null;
        $post->save();

        return $post;
    }
}
