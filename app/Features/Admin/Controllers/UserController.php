<?php

namespace App\Features\Admin\Controllers;

use App\Features\Post\Exports\PostExport;
use App\Features\Shared\Support\Like;
use App\Features\Shared\Support\PostFilters;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Admin hanya MEMANTAU (read-only): tidak ada aksi ubah/hapus artikel di sini. */
class UserController
{
    public function index(Request $request): Response
    {
        $filters = PostFilters::fromRequest($request);

        $users = User::query()
            ->withCount('posts')
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $like = Like::contains($filters['q']);

                $query->where(function ($q) use ($like) {
                    $q->whereRaw("LOWER(name) LIKE ? ESCAPE '\\'", [$like])
                        ->orWhereRaw("LOWER(username) LIKE ? ESCAPE '\\'", [$like])
                        ->orWhereRaw("LOWER(email) LIKE ? ESCAPE '\\'", [$like]);
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/UserList', ['users' => $users, 'filters' => $filters]);
    }

    public function posts(Request $request, User $user): Response
    {
        $filters = PostFilters::fromRequest($request);

        return Inertia::render('Admin/UserPostList', [
            'user' => $user->loadCount('posts')->only(['id', 'name', 'username', 'email', 'role', 'created_at', 'posts_count']),
            'posts' => $user->posts()->listing()->filter($filters)->latest()->paginate(15)->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function exportPosts(User $user, PostExport $export): StreamedResponse
    {
        return $export->download(
            $user->posts()->with('user:id,name')->latest()->get(),
            'artikel-'.$user->username.'-'.now()->format('Ymd').'.xlsx',
            withAuthor: true,
        );
    }
}
