<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'location' => 'required|string|max:255',
                'phone_number' => 'required|digits:10|unique:users,phone_number',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|confirmed|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.unique' => 'The phone number is already registered. Please use a different phone number.',
            'phone_number.digits' => 'The phone number must be exactly 10 digits.',
            'password.min' => 'Your password must have at least 8 characters.',
            'email.unique' => 'The email address is already taken.',
        ];
    }
}
