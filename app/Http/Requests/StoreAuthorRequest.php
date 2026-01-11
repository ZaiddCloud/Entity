<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuthorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by Policy
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'birth_year' => 'nullable|integer|min:-2000|max:' . (date('Y') + 1),
            'death_year' => 'nullable|integer|min:-2000|max:' . (date('Y') + 1),
        ];
    }
}
