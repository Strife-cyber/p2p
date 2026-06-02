<?php

namespace Database\Factories;

use App\Enums\FounderKey;
use App\Models\GovernanceAction;
use App\Models\GovernanceSignature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GovernanceSignature>
 */
class GovernanceSignatureFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'governance_action_id' => GovernanceAction::factory(),
            'founder_key' => fake()->randomElement(FounderKey::cases()),
            'created_at' => now(),
        ];
    }
}
