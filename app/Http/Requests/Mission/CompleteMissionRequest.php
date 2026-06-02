<?php

namespace App\Http\Requests\Mission;

use Illuminate\Foundation\Http\FormRequest;

class CompleteMissionRequest extends FormRequest
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
            'after_photo_urls' => ['required', 'array', 'min:1'],
            'after_photo_urls.*' => ['required', 'string', 'url', 'max:512'],
        ];
    }
}
