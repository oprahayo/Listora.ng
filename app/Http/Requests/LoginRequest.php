<?php

namespace App\Http\Requests;

use App\Domain\Auth\PhoneNormalizer;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identifier' => [
                'required',
                'string',
                'max:190',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! filter_var($value, FILTER_VALIDATE_EMAIL) && ! PhoneNormalizer::normalize($value)) {
                        $fail('Enter a valid email address or Nigerian phone number.');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'remember' => ['nullable', 'boolean'],
            'intent' => ['nullable', 'string', Rule::in(['list-property', 'chat', 'save-sync', 'dashboard'])],
            'return_to' => [
                'nullable',
                'string',
                'max:500',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $allowedPrefixes = ['/agent/', '/landlord/', '/tenant/', '/admin/'];
                    $isAllowed = $value === '/dashboard'
                        || collect($allowedPrefixes)->contains(fn (string $prefix): bool => str_starts_with($value, $prefix));

                    if (! str_starts_with($value, '/') || str_starts_with($value, '//') || ! $isAllowed) {
                        $fail('The return location is invalid.');
                    }
                },
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $identifier = trim((string) $this->input('identifier'));

        $this->merge([
            'identifier' => filter_var($identifier, FILTER_VALIDATE_EMAIL)
                ? strtolower($identifier)
                : $identifier,
        ]);
    }
}
