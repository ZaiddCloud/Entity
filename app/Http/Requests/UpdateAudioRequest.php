<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAudioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'author_ids' => 'nullable|array',
            'author_ids.*' => 'exists:authors,id',
            'publisher_id' => 'nullable|exists:publishers,id',
            'published_year' => 'nullable|integer|min:1000|max:' . (date('Y') + 1),
            'description' => 'nullable|string',
            'cover' => 'sometimes|nullable|image|max:2048',
            'file' => 'sometimes|nullable|mimes:mp3,wav|max:51200',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ];
    }
}
