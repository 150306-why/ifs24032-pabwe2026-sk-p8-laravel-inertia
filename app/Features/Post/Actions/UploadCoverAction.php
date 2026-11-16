<?php

namespace App\Features\Post\Actions;

use App\Features\Post\Models\Post;
use App\Features\Post\Services\CoverImageService;
use Illuminate\Http\UploadedFile;

class UploadCoverAction
{
    public function __construct(private CoverImageService $covers)
    {
    }

    public function execute(Post $post, UploadedFile $file): Post
    {
        $post->cover_image = $this->covers->store($file, $post->cover_image);
        $post->save();

        return $post;
    }
}
