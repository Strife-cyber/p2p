<?php

namespace Database\Factories;

use App\Models\Mission;
use App\Models\ProofFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProofFile>
 */
class ProofFileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mission_id' => Mission::factory(),
            'storage_url' => fake()->url().'/proofs/'.fake()->uuid().'.jpg',
            'captured_at' => now(),
        ];
    }
}
