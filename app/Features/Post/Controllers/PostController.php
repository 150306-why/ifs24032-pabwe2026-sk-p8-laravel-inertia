<?php

namespace App\Features\Post\Controllers;

use App\Features\Dashboard\Services\DashboardStatsService;
use App\Features\Post\Actions\CreatePostAction;
use App\Features\Post\Actions\DeletePostAction;
use App\Features\Post\Actions\TogglePublishAction;
use App\Features\Post\Actions\UpdatePostAction;
use App\Features\Post\Models\Post;
use App\Features\Post\Requests\SavePostRequest;
use App\Features\Shared\Support\PostFilters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PostController
{
    public function index(Request $request, DashboardStatsService $stats): Response
    {
        $user = $request->user();
        $filters = PostFilters::fromRequest($request);

        $posts = $user->posts()
            ->filter($filters)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Post/Index', [
            'posts' => $posts,
            'stats' => $stats->forUser($user),
            'filters' => $filters,
        ]);
    }

    public function store(SavePostRequest $request, CreatePostAction $action): RedirectResponse
    {
        $post = $action->execute($request->user(), $request->safe()->except('cover'), $request->file('cover'));

        return to_route('dashboard.posts.index')->with(
            'success',
            $post->is_published ? 'Artikel berhasil dipublikasikan.' : 'Artikel berhasil disimpan sebagai draft.'
        );
    }

    public function show(Post $post): Response
    {
        Gate::authorize('view', $post);

        return Inertia::render('Post/Show', ['post' => $post->load('user:id,name')]);
    }

    public function update(SavePostRequest $request, Post $post, UpdatePostAction $action): RedirectResponse
    {
        Gate::authorize('update', $post);

        $action->execute($post, $request->safe()->except('cover'), $request->file('cover'));

        return back()->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Post $post, DeletePostAction $action): RedirectResponse
    {
        Gate::authorize('delete', $post);

        $action->execute($post);

        return to_route('dashboard.posts.index')->with('success', 'Artikel berhasil dihapus.');
    }

    public function publish(Post $post, TogglePublishAction $action): RedirectResponse
    {
        Gate::authorize('update', $post);

        $post = $action->execute($post);

        return back()->with('success', $post->is_published ? 'Artikel dipublikasikan.' : 'Artikel dikembalikan menjadi draft.');
    }
}
