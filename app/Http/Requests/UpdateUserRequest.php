<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Gate handled in controller
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'password' => [
                'nullable',
                'confirmed',
                Password::min(8)->mixedCase()->numbers(),
            ],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name', Rule::notIn(['Admin'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'roles.array' => 'El formato de roles no es válido.',
            'roles.*.exists' => 'Uno o más roles seleccionados no existen.',
            'roles.*.not_in' => 'El rol Admin no puede asignarse a otros usuarios.',
        ];
    }
}
