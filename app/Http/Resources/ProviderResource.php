<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Provider
 */
class ProviderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->when(
                $this->relationLoaded('securityAccount') && $this->securityAccount?->relationLoaded('user'),
                fn () => $this->securityAccount->user->name,
            ),
            'current_badge' => $this->current_badge,
            'srt_score' => $this->srt_score,
            'activity_status' => $this->activity_status,
            'service_categories' => ServiceCategoryResource::collection($this->whenLoaded('serviceCategories')),
        ];
    }
}
