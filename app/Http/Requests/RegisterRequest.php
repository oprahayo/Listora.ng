<?php

namespace App\Http\Requests;

use App\Domain\Auth\PhoneNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => str((string) $this->input('email'))->trim()->lower()->toString(),
            'phone' => PhoneNormalizer::normalize((string) $this->input('phone')),
        ]);
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in(['agent', 'landlord', 'tenant'])],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^234[789]\d{9}$/', Rule::unique('users', 'phone')],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.unique' => 'An account already exists with these details.',
            'email.unique' => 'An account already exists with these details.',
            'phone.regex' => 'Enter a valid Nigerian phone number.',
            'terms.accepted' => 'Accept the Terms and Privacy Policy to continue.',
        ];
    }
}
