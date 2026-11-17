<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'courses' => ['nullable', 'array'],
            'courses.*' => ['integer', 'exists:courses,id'],
        ];
    }
}
