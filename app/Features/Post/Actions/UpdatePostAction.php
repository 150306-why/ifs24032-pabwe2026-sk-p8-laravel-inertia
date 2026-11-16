<?php

namespace App\Features\Post\Actions;

use App\Features\Post\Models\Post;
use App\Features\Post\Services\CoverImageService;
use App\Features\Post\Services\SlugService;
use Illuminate\Http\UploadedFile;

class UpdatePostAction
{
    public function __construct(private SlugService $slugs, private CoverImageService $covers)
    {
    }

    /** @param array{title: string, category: string, content: string, is_published: bool} $data */
    public function execute(Post $post, array $data, ?UploadedFile $cover = null): Post
    {
        $published = (bool) $data['is_published'];

        $post->fill([
            'title' => $data['title'],
            'category' => $data['category'],
            'content' => $data['content'],
            'is_published' => $published,
            // Tanggal publikasi dipertahankan selama artikel tetap Published.
            'published_at' => $published ? ($post->published_at ?? now()) : null,
        ]);

        if ($post->isDirty('title')) {
            $post->slug = $this->slugs->unique($data['title'], $post->id);
        }

        if ($cover) {
            $post->cover_image = $this->covers->store($cover, $post->cover_image);
        }

        $post->save();

        return $post;
    }
}
