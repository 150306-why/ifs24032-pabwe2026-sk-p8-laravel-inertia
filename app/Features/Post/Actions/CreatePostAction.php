<?php

namespace App\Features\Post\Actions;

use App\Features\Post\Models\Post;
use App\Features\Post\Services\CoverImageService;
use App\Features\Post\Services\SlugService;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class CreatePostAction
{
    public function __construct(private SlugService $slugs, private CoverImageService $covers)
    {
    }

    /** @param array{title: string, category: string, content: string, is_published?: bool} $data */
    public function execute(User $user, array $data, ?UploadedFile $cover = null): Post
    {
        $published = (bool) ($data['is_published'] ?? false);

        return $user->posts()->create([
            'title' => $data['title'],
            'slug' => $this->slugs->unique($data['title']),
            'category' => $data['category'],
            'content' => $data['content'],
            'cover_image' => $cover ? $this->covers->store($cover) : null,
            'is_published' => $published,
            'published_at' => $published ? now() : null,
        ]);
    }
}
