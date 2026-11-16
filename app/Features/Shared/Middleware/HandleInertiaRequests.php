<?php

namespace App\Features\Shared\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'home' => $user->homeUrl(),
                ] : null,
            ],
            'flash' => fn () => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'import_errors' => $request->session()->get('import_errors'),
                'ai_results' => $request->session()->get('ai_results'),
            ],
            'categories' => config('blog.categories'),
            'ai' => ['max' => config('blog.ai_max_articles')],
        ];
    }
}
