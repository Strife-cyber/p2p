<?php

namespace Database\Factories;

use App\Enums\FlowType;
use App\Enums\ValidationResult;
use App\Models\ProofFile;
use App\Models\ProofValidation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProofValidation>
 */
class ProofValidationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'proof_file_id' => ProofFile::factory(),
            'flow_type' => fake()->randomElement(FlowType::cases()),
            'validation_result' => ValidationResult::AutoValidated,
            'validator_id' => User::factory(),
            'created_at' => now(),
        ];
    }
}
