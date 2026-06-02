<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientReliabilityEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientReliabilityEvent>
 */
class ClientReliabilityEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'event_type' => fake()->randomElement(['late_cancellation', 'non_payment', 'mission_completed']),
            'score_impact' => fake()->randomFloat(2, -5, 5),
            'description' => fake()->optional()->sentence(),
            'created_at' => now(),
        ];
    }
}
