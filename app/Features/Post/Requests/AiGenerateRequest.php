<?php

namespace App\Features\Post\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AiGenerateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'topic' => ['required', 'string', 'min:3', 'max:200'],
            'category' => ['required', 'string', 'max:60'],
            'count' => ['required', 'integer', 'min:1', 'max:'.config('blog.ai_max_articles')],
        ];
    }

    public function attributes(): array
    {
        return ['topic' => 'topik', 'category' => 'kategori', 'count' => 'jumlah artikel'];
    }
}
