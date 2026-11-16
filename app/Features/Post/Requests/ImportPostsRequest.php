<?php

namespace App\Features\Post\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportPostsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return ['file' => 'file Excel'];
    }
}
