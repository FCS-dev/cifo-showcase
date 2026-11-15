<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
        // return Gate::allows('categories.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'array'],
            'name.es' => ['required', 'string', 'max:255'],
            'name.ca' => ['required', 'string', 'max:255'],
            'badge' => ['required', 'array'],
            'badge.es' => ['required', 'string', 'max:10'],
            'badge.ca' => ['required', 'string', 'max:10'],
            'short_description' => ['required', 'array'],
            'short_description.es' => ['required', 'string', 'max:150'],
            'short_description.ca' => ['required', 'string', 'max:150'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            // 'icon' => ['required', 'string', 'max:255'],
        ];
    }
}
