<?php

namespace App\Features\Post\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AiSaveRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'articles' => ['required', 'array', 'min:1', 'max:'.config('blog.ai_max_articles')],
            'articles.*.title' => ['required', 'string', 'max:150'],
            'articles.*.category' => ['required', 'string', 'max:60'],
            'articles.*.content' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return ['articles' => 'artikel', 'articles.*.title' => 'judul', 'articles.*.category' => 'kategori', 'articles.*.content' => 'konten'];
    }
}
