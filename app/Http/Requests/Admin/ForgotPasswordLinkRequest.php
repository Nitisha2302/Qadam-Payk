<?php


namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordLinkRequest extends FormRequest
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
           'email' => 'required|email',
        ];
    }
        public function messages(): array
    {
        return [
            'email.required' => 'E-mail is vereist.',
            'email.email' => 'Voer een geldig e-mailadres in.'
        ];
    }
}
