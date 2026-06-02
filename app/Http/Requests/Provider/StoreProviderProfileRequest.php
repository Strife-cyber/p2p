<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProviderProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'service_category_ids' => ['required', 'array', 'min:1'],
            'service_category_ids.*' => ['uuid', Rule::exists('service_categories', 'id')],
        ];
    }
}
