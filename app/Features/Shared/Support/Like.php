<?php

namespace App\Features\Shared\Support;

final class Like
{
    /** Pola LIKE "mengandung" huruf kecil dengan karakter wildcard di-escape (gunakan ESCAPE '\'). */
    public static function contains(string $term): string
    {
        return '%'.mb_strtolower(addcslashes($term, '%_\\')).'%';
    }
}
