<?php

namespace Database\Factories;

use App\Enums\ClientType;
use App\Models\Client;
use App\Models\SecurityAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'security_account_id' => SecurityAccount::factory(),
            'client_type' => fake()->randomElement(ClientType::cases()),
        ];
    }
}
