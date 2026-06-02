<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\FinancialTransaction;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialTransaction>
 */
class FinancialTransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wallet_id' => Wallet::factory(),
            'amount' => fake()->randomFloat(2, -500, 500),
            'transaction_type' => fake()->randomElement(TransactionType::cases()),
            'external_reference' => fake()->optional()->uuid(),
            'created_at' => now(),
        ];
    }
}
