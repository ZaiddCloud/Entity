<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'author_ids' => 'nullable|array',
            'author_ids.*' => 'exists:authors,id',
            'publisher_id' => 'nullable|exists:publishers,id',
            'isbn' => 'nullable|string|max:20',
            'pages' => 'nullable|integer|min:1',
            'published_year' => 'nullable|integer|min:1000|max:' . (date('Y') + 1),
            'edition_number' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'cover' => 'nullable|image|max:2048', // 2MB Max
            'file' => 'nullable|mimes:pdf|max:51200', // 50MB
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ];
    }
}
