<?php

namespace App\Http\Requests;

use App\Domain\Auth\PhoneNormalizer;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class OtpRequest extends FormRequest
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
        ];
    }
}
