<?php

namespace Database\Factories;

use App\Enums\GovernanceActionStatus;
use App\Enums\GovernanceActionType;
use App\Models\GovernanceAction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GovernanceAction>
 */
class GovernanceActionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'action_type' => fake()->randomElement(GovernanceActionType::cases()),
            'raw_payload' => json_encode(['reason' => fake()->sentence()]),
            'action_status' => GovernanceActionStatus::PendingSignatures,
            'created_executed_at' => now(),
        ];
    }
}
