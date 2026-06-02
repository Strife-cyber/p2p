<?php

namespace Database\Factories;

use App\Models\SecurityAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SecurityAccount>
 */
class SecurityAccountFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'real_phone' => fake()->unique()->e164PhoneNumber(),
            'proxy_number' => fake()->unique()->e164PhoneNumber(),
            'device_fingerprint' => hash('sha256', fake()->uuid()),
            'national_id_hash' => hash('sha256', fake()->uuid()),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (SecurityAccount $account): void {
            if ($account->user_id === null) {
                return;
            }

            $phone = User::query()->find($account->user_id)?->phone;

            if ($phone !== null) {
                $account->real_phone = $phone;
            }
        });
    }
}
