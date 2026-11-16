<?php

namespace App\Features\Dashboard\Services;

use App\Models\User;

class DashboardStatsService
{
    /** @return array{total: int, published: int, draft: int} */
    public function forUser(User $user): array
    {
        $total = $user->posts()->count();
        $published = $user->posts()->where('is_published', true)->count();

        return ['total' => $total, 'published' => $published, 'draft' => $total - $published];
    }
}
