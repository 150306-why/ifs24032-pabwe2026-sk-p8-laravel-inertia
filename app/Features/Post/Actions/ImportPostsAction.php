<?php

namespace App\Features\Post\Actions;

use App\Features\Post\Imports\PostImport;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ImportPostsAction
{
    public function __construct(private PostImport $import, private CreatePostAction $create)
    {
    }

    /** @return array{created: int, errors: array<int, string>} */
    public function execute(User $user, UploadedFile $file): array
    {
        $result = $this->import->parse($file->getRealPath());

        if ($result['errors'] !== []) {
            return ['created' => 0, 'errors' => $result['errors']];
        }

        DB::transaction(function () use ($user, $result) {
            foreach ($result['rows'] as $row) {
                $this->create->execute($user, $row);
            }
        });

        return ['created' => count($result['rows']), 'errors' => []];
    }
}
