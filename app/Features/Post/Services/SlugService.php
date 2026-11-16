<?php

namespace App\Features\Post\Services;

use App\Features\Post\Models\Post;
use Illuminate\Support\Str;

class SlugService
{
    /** Slug unik; judul kembar menjadi "judul", "judul-2", "judul-3", dst. */
    public function unique(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'artikel';
        $slug = $base;
        $suffix = 2;

        while ($this->exists($slug, $ignoreId)) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function exists(string $slug, ?int $ignoreId): bool
    {
        return Post::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }
}
