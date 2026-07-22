<?php

namespace App\Http\Requests;

use App\Domain\Properties\PropertyTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropertyFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:60'],
            'city' => ['nullable', 'string', 'max:60'],
            'area' => ['nullable', 'string', 'max:80'],
            'type' => ['nullable', Rule::in(PropertyTypes::slugs())],
            'min_price' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'max_price' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'bedrooms' => ['nullable', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'furnishing' => ['nullable', Rule::in(['unfurnished', 'semi-furnished', 'furnished'])],
            'amenities' => ['nullable', 'array', 'max:8'],
            'amenities.*' => [Rule::in(['water', 'security', 'parking', 'power', 'road'])],
            'sort' => ['nullable', Rule::in(['recommended', 'latest', 'price_asc', 'price_desc'])],
            'page' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                if ($this->filled('min_price') && $this->filled('max_price') && (int) $this->max_price < (int) $this->min_price) {
                    $validator->errors()->add('max_price', 'The maximum price must be at least the minimum price.');
                }
            },
        ];
    }
}
