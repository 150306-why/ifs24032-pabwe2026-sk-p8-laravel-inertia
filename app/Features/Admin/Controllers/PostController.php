<?php

namespace App\Features\Admin\Controllers;

use App\Features\Post\Exports\PostExport;
use App\Features\Post\Models\Post;
use App\Features\Shared\Support\PostFilters;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PostController
{
    public function index(Request $request): Response
    {
        $filters = PostFilters::fromRequest($request);

        return Inertia::render('Admin/PostList', [
            'posts' => Post::query()
                ->with('user:id,name,username')
                ->listing()
                ->filter($filters)
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'filters' => $filters,
            'allCategories' => Post::query()->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function export(PostExport $export): StreamedResponse
    {
        return $export->download(
            Post::query()->with('user:id,name')->latest()->get(),
            'semua-artikel-'.now()->format('Ymd').'.xlsx',
            withAuthor: true,
        );
    }
}
