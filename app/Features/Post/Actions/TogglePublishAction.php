<?php

namespace App\Features\Post\Actions;

use App\Features\Post\Models\Post;

class TogglePublishAction
{
    public function execute(Post $post): Post
    {
        $post->is_published = ! $post->is_published;
        $post->published_at = $post->is_published ? now() : null;
        $post->save();

        return $post;
    }
}
