<?php

namespace Database\Factories;

use App\Enums\EscrowStatus;
use App\Models\EscrowLedger;
use App\Models\Mission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EscrowLedger>
 */
class EscrowLedgerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mission_id' => Mission::factory(),
            'total_amount' => fake()->randomFloat(2, 100, 10000),
            'escrow_status' => EscrowStatus::Blocked,
            'transaction_reference' => fake()->uuid(),
            'locked_at' => now(),
            'released_at' => null,
        ];
    }
}
