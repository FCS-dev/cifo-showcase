<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
        // return Gate::allows('categories.update');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'array'],
            'name.es' => ['required', 'string', 'max:255'],
            'name.ca' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            // 'icon' => ['required', 'string', 'max:255'],
        ];
    }
}
