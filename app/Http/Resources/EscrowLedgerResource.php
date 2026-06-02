<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\EscrowLedger
 */
class EscrowLedgerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mission_id' => $this->mission_id,
            'total_amount' => $this->total_amount,
            'escrow_status' => $this->escrow_status,
            'transaction_reference' => $this->transaction_reference,
            'locked_at' => $this->locked_at,
            'released_at' => $this->released_at,
        ];
    }
}
