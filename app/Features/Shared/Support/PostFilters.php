<?php

namespace App\Features\Shared\Support;

use Illuminate\Http\Request;

final class PostFilters
{
    /** @return array{q: string, category: string, status: string} */
    public static function fromRequest(Request $request): array
    {
        return [
            'q' => self::value($request, 'q'),
            'category' => self::value($request, 'category'),
            'status' => self::value($request, 'status'),
        ];
    }

    private static function value(Request $request, string $key): string
    {
        $value = $request->query($key);

        return is_string($value) ? trim($value) : '';
    }
}
