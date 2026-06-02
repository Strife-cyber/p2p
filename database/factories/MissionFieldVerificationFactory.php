<?php

namespace Database\Factories;

use App\Models\Mission;
use App\Models\MissionFieldVerification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MissionFieldVerification>
 */
class MissionFieldVerificationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mission_id' => Mission::factory(),
            'theoretical_latitude' => fake()->latitude(),
            'theoretical_longitude' => fake()->longitude(),
            'checkin_latitude' => null,
            'checkin_longitude' => null,
            'checked_in_at' => null,
        ];
    }
}
