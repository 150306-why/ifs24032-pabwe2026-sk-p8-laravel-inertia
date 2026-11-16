<?php

namespace App\Features\Post\Controllers;

use App\Features\Post\Actions\ImportPostsAction;
use App\Features\Post\Exports\PostExport;
use App\Features\Post\Requests\ImportPostsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PostTransferController
{
    public function export(Request $request, PostExport $export): StreamedResponse
    {
        $user = $request->user();

        return $export->download(
            $user->posts()->latest()->get(),
            'artikel-'.$user->username.'-'.now()->format('Ymd').'.xlsx',
        );
    }

    public function import(ImportPostsRequest $request, ImportPostsAction $action): RedirectResponse
    {
        $result = $action->execute($request->user(), $request->file('file'));

        if ($result['errors'] !== []) {
            return back()
                ->with('error', 'Impor dibatalkan. Tidak ada artikel yang disimpan karena terdapat data tidak valid.')
                ->with('import_errors', $result['errors']);
        }

        return back()->with('success', "{$result['created']} artikel berhasil diimpor.");
    }
}
