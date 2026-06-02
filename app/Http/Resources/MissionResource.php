<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Mission
 */
class MissionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User|null $user */
        $user = $request->user();
        $isClient = $user?->client?->id === $this->client_id;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'intervention_address' => $this->intervention_address,
            'estimated_price' => $this->estimated_price,
            'final_price' => $this->final_price,
            'urgency_level' => $this->urgency_level,
            'execution_mode' => $this->execution_mode,
            'lifecycle_status' => $this->lifecycle_status,
            'pairing_code' => $this->when($isClient, $this->pairing_code),
            'scheduled_at' => $this->scheduled_at,
            'completed_at' => $this->completed_at,
            'warranty_expires_at' => $this->warranty_expires_at,
            'client' => new ClientResource($this->whenLoaded('client')),
            'provider' => new ProviderResource($this->whenLoaded('provider')),
            'service_category' => new ServiceCategoryResource($this->whenLoaded('serviceCategory')),
            'escrow_ledger' => new EscrowLedgerResource($this->whenLoaded('escrowLedger')),
            'field_verification' => $this->whenLoaded('fieldVerification'),
            'applications' => $this->whenLoaded('applications'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
