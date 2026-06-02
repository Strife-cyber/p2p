<?php

namespace Database\Seeders;

use App\Enums\FounderKey;
use App\Enums\LifecycleStatus;
use App\Models\Client;
use App\Models\GovernanceAction;
use App\Models\Mission;
use App\Models\Provider;
use App\Models\SecurityAccount;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\Wallet;
use App\Support\PhoneNumber;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class P2pDatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the P2P domain schema with sample data.
     */
    public function run(): void
    {
        $categories = ServiceCategory::factory()
            ->count(5)
            ->sequence(
                ['name' => 'Plumbing', 'description' => 'Water systems and pipe repairs.'],
                ['name' => 'Electrical', 'description' => 'Wiring, panels, and electrical safety.'],
                ['name' => 'HVAC', 'description' => 'Heating, ventilation, and air conditioning.'],
                ['name' => 'Carpentry', 'description' => 'Woodwork, framing, and custom fittings.'],
                ['name' => 'Painting', 'description' => 'Interior and exterior surface finishing.'],
            )
            ->create();

        $admin = User::factory()->withEmail()->create([
            'name' => 'Platform Admin',
            'phone' => PhoneNumber::normalize('+237670000001'),
            'email' => 'admin@p2p.test',
        ]);

        $clientUser = User::factory()->create([
            'name' => 'Demo Client',
            'phone' => PhoneNumber::normalize('+237670000002'),
        ]);

        $clientAccount = SecurityAccount::factory()->for($clientUser)->create();
        $client = Client::factory()->for($clientAccount, 'securityAccount')->create();

        $providerUser = User::factory()->create([
            'name' => 'Demo Provider',
            'phone' => PhoneNumber::normalize('+237670000003'),
        ]);

        $providerAccount = SecurityAccount::factory()->for($providerUser)->create();
        $provider = Provider::factory()->for($providerAccount, 'securityAccount')->create();

        $provider->serviceCategories()->attach($categories->random(2)->pluck('id'));

        Wallet::factory()->for($clientUser)->create(['current_balance' => 2500]);
        Wallet::factory()->for($providerUser)->create(['current_balance' => 1200]);
        Wallet::factory()->for($admin)->create(['current_balance' => 0]);

        Mission::factory()
            ->for($client)
            ->for($provider)
            ->for($categories->first())
            ->create([
                'lifecycle_status' => LifecycleStatus::Assigned,
                'title' => 'Fix kitchen sink leak',
            ]);

        Mission::factory()
            ->count(3)
            ->for($client)
            ->for($categories->random())
            ->create([
                'provider_id' => null,
                'lifecycle_status' => LifecycleStatus::Published,
            ]);

        Provider::factory()
            ->count(3)
            ->create()
            ->each(function (Provider $extraProvider) use ($categories): void {
                $extraProvider->serviceCategories()->attach(
                    $categories->random(fake()->numberBetween(1, 3))->pluck('id')
                );
            });

        Client::factory()->count(2)->create();

        $governanceAction = GovernanceAction::factory()->create();

        foreach (FounderKey::cases() as $founderKey) {
            $governanceAction->signatures()->create([
                'founder_key' => $founderKey,
                'created_at' => now(),
            ]);
        }
    }
}
