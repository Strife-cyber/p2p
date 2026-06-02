<?php

namespace Database\Factories;

use App\Enums\ActivityStatus;
use App\Enums\BadgeType;
use App\Models\Provider;
use App\Models\SecurityAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Provider>
 */
class ProviderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'security_account_id' => SecurityAccount::factory(),
            'current_badge' => BadgeType::Grey,
            'badge_modified_at' => now(),
            'badge_expires_at' => now()->addYear(),
            'srt_score' => fake()->randomFloat(4, 0, 100),
            'missions_without_dispute_count' => fake()->numberBetween(0, 50),
            'activity_status' => ActivityStatus::Available,
        ];
    }
}
