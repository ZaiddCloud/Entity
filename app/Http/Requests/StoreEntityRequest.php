<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use App\Models\Entity;
use App\Models\Book;

class StoreEntityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by Controller/Policy
    }

    /**
     * Prepare the data for validation.
     * We inject the type if it's missing, trying to guess from the route or controller context if possible,
     * but usually the controller should have merged it. 
     * However, FormRequest runs BEFORE Controller code.
     * So we might rely on the client sending 'type' OR separate requests per type.
     * For now, we will make 'type' optional here if we trust the controller to add it later for the Service,
     * OR we check if the route matches a specific resource.
     */
    protected function prepareForValidation()
    {
        // Example: if route is books.store, we imply type=book
        if ($this->routeIs('books.store')) {
            $this->merge(['type' => 'book']);
        } elseif ($this->routeIs('audios.store')) {
            $this->merge(['type' => 'audio']);
        } elseif ($this->routeIs('videos.store')) {
            $this->merge(['type' => 'video']);
        } elseif ($this->routeIs('manuscripts.store')) {
            $this->merge(['type' => 'manuscript']);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'type' => 'required|in:book,video,audio,manuscript',
            'author' => 'nullable|string|max:255',
            'century' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'cover' => 'nullable|image|max:2048', // 2MB Max
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ];

        $type = $this->input('type');

        if ($type === 'book' || $type === 'manuscript') {
             $rules['file'] = 'nullable|mimes:pdf|max:51200'; // 50MB
        } elseif ($type === 'audio') {
             $rules['file'] = 'nullable|mimes:mp3,wav|max:51200';
        } elseif ($type === 'video') {
             $rules['file'] = 'nullable|mimes:mp4,mov,avi|max:102400'; // 100MB
        }

        return $rules;
    }
}
