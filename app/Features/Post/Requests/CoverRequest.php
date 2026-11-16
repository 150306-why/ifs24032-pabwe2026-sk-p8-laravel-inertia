<?php

namespace App\Features\Post\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CoverRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'cover' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
