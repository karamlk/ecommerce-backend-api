<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'identifier' => 'required|string',
            'password'   => 'required|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'Please enter a password.',
            'password.min'      => 'Your password must have at least 8 characters.',
        ];
    }
}
