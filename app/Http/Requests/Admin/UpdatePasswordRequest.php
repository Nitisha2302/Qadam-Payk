<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
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
             'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required_with:password',
        ];
    }
       public function messages(): array
    {
        return [
            'password.required' => 'Wachtwoord is vereist.',
            'password.string' => 'Het wachtwoord moet een geldige tekst zijn.',
            'password.min' => 'Het wachtwoord moet ten minste 8 tekens bevatten.',
            'password.confirmed' => 'De wachtwoorden komen niet overeen.',
            'password_confirmation.required_with' => 'Het bevestigingswachtwoord is vereist wanneer het wachtwoord is ingevuld.',
        ];
    }
}
