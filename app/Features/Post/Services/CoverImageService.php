<?php

namespace App\Features\Post\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CoverImageService
{
    /** Simpan cover baru (nama acak) dan hapus cover lama bila ada. */
    public function store(UploadedFile $file, ?string $old = null): string
    {
        $path = (string) $file->store('covers', 'public');

        $this->delete($old);

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
