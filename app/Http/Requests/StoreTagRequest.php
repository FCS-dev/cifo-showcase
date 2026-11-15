<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'array'],
            'name.es' => ['required', 'string', 'max:150', Rule::unique('tags', 'name->es')],
            'name.ca' => ['required', 'string', 'max:150', Rule::unique('tags', 'name->ca')],
        ];
    }
}
