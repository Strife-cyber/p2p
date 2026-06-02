<?php

namespace Database\Factories;

use App\Enums\ExecutionMode;
use App\Enums\LifecycleStatus;
use App\Enums\UrgencyLevel;
use App\Models\Client;
use App\Models\Mission;
use App\Models\Provider;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Mission>
 */
class MissionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $scheduledAt = fake()->dateTimeBetween('+1 day', '+2 weeks');

        return [
            'client_id' => Client::factory(),
            'provider_id' => null,
            'service_category_id' => ServiceCategory::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'intervention_address' => fake()->address(),
            'estimated_price' => fake()->randomFloat(2, 50, 5000),
            'final_price' => null,
            'urgency_level' => UrgencyLevel::Normal,
            'execution_mode' => fake()->randomElement(ExecutionMode::cases()),
            'lifecycle_status' => LifecycleStatus::Published,
            'pairing_code' => strtoupper(Str::random(8)),
            'scheduled_at' => $scheduledAt,
            'completed_at' => null,
            'warranty_expires_at' => null,
        ];
    }

    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider_id' => Provider::factory(),
            'lifecycle_status' => LifecycleStatus::Assigned,
        ]);
    }
}
