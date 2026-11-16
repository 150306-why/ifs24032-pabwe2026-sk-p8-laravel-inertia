<?php

namespace App\Features\Post\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:60'],
            'content' => ['required', 'string'],
            'is_published' => ['required', 'boolean'],
            'cover' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return ['title' => 'judul', 'category' => 'kategori', 'content' => 'konten', 'is_published' => 'status', 'cover' => 'cover'];
    }
}
