<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAudioRequest extends FormRequest
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
            'published_year' => 'nullable|integer|min:1000|max:' . (date('Y') + 1),
            'description' => 'nullable|string',
            'cover' => 'nullable|image|max:2048',
            'file' => 'nullable|mimes:mp3,wav|max:51200', // 50MB
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ];
    }
}
