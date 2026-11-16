<?php

namespace App\Features\Post\Controllers;

use App\Features\Post\Actions\DeleteCoverAction;
use App\Features\Post\Actions\UploadCoverAction;
use App\Features\Post\Models\Post;
use App\Features\Post\Requests\CoverRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class PostCoverController
{
    public function store(CoverRequest $request, Post $post, UploadCoverAction $action): RedirectResponse
    {
        Gate::authorize('update', $post);

        $action->execute($post, $request->file('cover'));

        return back()->with('success', 'Cover berhasil diunggah.');
    }

    public function destroy(Post $post, DeleteCoverAction $action): RedirectResponse
    {
        Gate::authorize('update', $post);

        $action->execute($post);

        return back()->with('success', 'Cover berhasil dihapus.');
    }
}
