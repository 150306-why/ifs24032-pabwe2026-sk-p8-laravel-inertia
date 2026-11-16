<?php

namespace App\Features\Post\Controllers;

use App\Features\Post\Actions\GeneratePostsAction;
use App\Features\Post\Actions\SaveAiPostsAction;
use App\Features\Post\Exceptions\AiGenerationException;
use App\Features\Post\Requests\AiGenerateRequest;
use App\Features\Post\Requests\AiSaveRequest;
use Illuminate\Http\RedirectResponse;

class PostAiController
{
    public function generate(AiGenerateRequest $request, GeneratePostsAction $action): RedirectResponse
    {
        try {
            $articles = $action->execute(
                $request->validated('topic'),
                $request->validated('category'),
                (int) $request->validated('count'),
            );
        } catch (AiGenerationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with([
            'success' => count($articles).' draft artikel berhasil dihasilkan. Periksa dan edit sebelum menyimpan.',
            'ai_results' => $articles,
        ]);
    }

    public function save(AiSaveRequest $request, SaveAiPostsAction $action): RedirectResponse
    {
        $count = $action->execute($request->user(), $request->validated('articles'));

        return to_route('dashboard.posts.index')->with('success', "{$count} artikel AI disimpan sebagai draft.");
    }
}
