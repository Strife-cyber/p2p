<?php

namespace Database\Factories;

use App\Models\Provider;
use App\Models\ProviderTracking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderTracking>
 */
class ProviderTrackingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider_id' => Provider::factory(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'recorded_at' => now(),
        ];
    }
}
