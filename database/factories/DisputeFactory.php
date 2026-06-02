<?php

namespace Database\Factories;

use App\Enums\AnomalyType;
use App\Enums\DisputeStatus;
use App\Models\Dispute;
use App\Models\Mission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dispute>
 */
class DisputeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mission_id' => Mission::factory(),
            'anomaly_type' => fake()->randomElement(AnomalyType::cases()),
            'dispute_status' => DisputeStatus::Open,
            'arbitrator_id' => null,
            'decision_notes' => null,
            'srt_penalty' => 0,
            'triggered_at' => now(),
        ];
    }
}
