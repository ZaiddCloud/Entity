<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEntityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Handled by Controller/Policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'sometimes|string|max:255',
            'author' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'cover' => 'sometimes|nullable|image|max:2048',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ];

        // Determine type from route binding
        $fileRules = 'sometimes|nullable|file|max:102400';
        
        if ($this->route('book') || $this->route('manuscript')) {
            $fileRules = 'sometimes|nullable|mimes:pdf|max:51200';
        } elseif ($this->route('audio')) {
             $fileRules = 'sometimes|nullable|mimes:mp3,wav|max:51200';
        } elseif ($this->route('video')) {
             $fileRules = 'sometimes|nullable|mimes:mp4,mov,avi|max:102400';
        }

        $rules['file'] = $fileRules;

        return $rules;
    }
}
