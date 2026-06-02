<?php

namespace App\Http\Requests\Mission;

use App\Enums\ExecutionMode;
use App\Enums\UrgencyLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMissionRequest extends FormRequest
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
            'service_category_id' => ['required', 'uuid', Rule::exists('service_categories', 'id')],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'intervention_address' => ['required', 'string'],
            'estimated_price' => ['required', 'numeric', 'min:0'],
            'urgency_level' => ['nullable', Rule::enum(UrgencyLevel::class)],
            'execution_mode' => ['required', Rule::enum(ExecutionMode::class)],
            'scheduled_at' => ['required', 'date'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}
