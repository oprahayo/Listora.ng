<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OtpConfirmRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string', 'max:255'],
            'code' => ['required', 'digits:6'],
            'remember' => ['nullable', 'boolean'],
        ];
    }
}
