<?php

namespace App\Features\Post\Controllers;

use App\Features\Post\Models\Post;
use App\Features\Shared\Support\PostFilters;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicPostController
{
    public function home(): Response
    {
        $base = fn () => Post::published()->listing()->with('user:id,name');

        return Inertia::render('Public/Home', [
            'latest' => $base()->latest('published_at')->limit(6)->get(),
            'featured' => $base()->orderByRaw('(cover_image is null)')->latest('published_at')->limit(3)->get(),
            'categories' => Post::published()
                ->select('category')
                ->selectRaw('count(*) as total')
                ->groupBy('category')
                ->orderByDesc('total')
                ->limit(8)
                ->get(),
        ]);
    }

    public function index(Request $request): Response
    {
        $filters = PostFilters::fromRequest($request);

        return Inertia::render('Public/PostIndex', [
            'posts' => Post::published()
                ->listing()
                ->with('user:id,name')
                ->filter($filters)
                ->latest('published_at')
                ->paginate(9)
                ->withQueryString(),
            'filters' => $filters,
            'publicCategories' => Post::published()->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function show(string $slug): Response
    {
        $post = Post::published()->with('user:id,name')->where('slug', $slug)->firstOrFail();

        return Inertia::render('Public/PostShow', [
            'post' => $post,
            'related' => Post::published()
                ->listing()
                ->with('user:id,name')
                ->where('category', $post->category)
                ->whereKeyNot($post->id)
                ->latest('published_at')
                ->limit(3)
                ->get(),
        ]);
    }
}
