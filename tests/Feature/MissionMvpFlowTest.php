<?php

use App\Enums\LifecycleStatus;
use App\Models\Client;
use App\Models\Mission;
use App\Models\Provider;
use App\Models\SecurityAccount;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

function createClientUser(): array
{
    $user = User::factory()->create([
        'phone' => PhoneNumber::normalize('+237'.fake()->unique()->numerify('#########')),
        'password' => Hash::make('password'),
    ]);

    SecurityAccount::factory()->for($user)->create();

    $client = Client::factory()->create([
        'security_account_id' => $user->id,
    ]);

    return [$user, $client];
}

function createProviderUser(ServiceCategory $category): array
{
    $user = User::factory()->create([
        'phone' => PhoneNumber::normalize('+237'.fake()->unique()->numerify('#########')),
        'password' => Hash::make('password'),
    ]);

    SecurityAccount::factory()->for($user)->create();

    $provider = Provider::factory()->create([
        'security_account_id' => $user->id,
    ]);

    $provider->serviceCategories()->attach($category->id);

    return [$user, $provider];
}

it('runs the nominal mission MVP flow end to end', function () {
    $category = ServiceCategory::factory()->create();
    [$clientUser, $client] = createClientUser();
    [$providerUser, $provider] = createProviderUser($category);

    $clientToken = $clientUser->createToken('mobile')->plainTextToken;
    $providerToken = $providerUser->createToken('mobile')->plainTextToken;

    $this->withToken($clientToken)
        ->postJson('/api/wallet/sandbox-deposit', ['amount' => 5000])
        ->assertSuccessful();

    $missionResponse = $this->withToken($clientToken)
        ->postJson('/api/missions', [
            'service_category_id' => $category->id,
            'title' => 'Fix leaking pipe',
            'intervention_address' => 'Akwa, Douala',
            'estimated_price' => 1000,
            'execution_mode' => 'classic',
            'scheduled_at' => now()->addDay()->toISOString(),
            'latitude' => 4.0511,
            'longitude' => 9.7679,
        ])
        ->assertCreated();

    $missionId = $missionResponse->json('data.id');

    $this->withToken($clientToken)
        ->postJson("/api/missions/{$missionId}/escrow", [
            'payment_reference' => 'sandbox-momo-001',
        ])
        ->assertCreated();

    Auth::forgetGuards();

    $this->withToken($providerToken)
        ->postJson("/api/missions/{$missionId}/applications")
        ->assertCreated();

    Auth::forgetGuards();

    $this->withToken($clientToken)
        ->postJson("/api/missions/{$missionId}/assign", [
            'provider_id' => $provider->id,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.lifecycle_status', LifecycleStatus::Assigned->value);

    Auth::forgetGuards();

    $this->withToken($providerToken)
        ->postJson("/api/missions/{$missionId}/check-in", [
            'latitude' => 4.0511,
            'longitude' => 9.7679,
            'selfie_proof_url' => 'https://cdn.example.com/selfie.jpg',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.lifecycle_status', LifecycleStatus::CheckInInProgress->value);

    $pairingCode = Mission::query()->find($missionId)->pairing_code;

    $this->withToken($providerToken)
        ->postJson("/api/missions/{$missionId}/pair", [
            'pairing_code' => $pairingCode,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.lifecycle_status', LifecycleStatus::InProgress->value);

    $this->withToken($providerToken)
        ->postJson("/api/missions/{$missionId}/complete", [
            'after_photo_urls' => ['https://cdn.example.com/after.jpg'],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.lifecycle_status', LifecycleStatus::Completed->value);

    Auth::forgetGuards();

    $this->withToken($clientToken)
        ->postJson("/api/missions/{$missionId}/validate")
        ->assertSuccessful()
        ->assertJsonPath('data.lifecycle_status', LifecycleStatus::UnderWarranty->value);

    Mission::query()->whereKey($missionId)->update([
        'warranty_expires_at' => now()->subMinute(),
    ]);

    $this->withToken($clientToken)
        ->postJson("/api/missions/{$missionId}/warranty/close")
        ->assertSuccessful()
        ->assertJsonPath('data.lifecycle_status', LifecycleStatus::Closed->value);
});

it('hides pairing code from providers', function () {
    $category = ServiceCategory::factory()->create();
    [$clientUser, $client] = createClientUser();
    [$providerUser, $provider] = createProviderUser($category);

    $mission = Mission::factory()->for($client)->for($category)->create([
        'provider_id' => $provider->id,
        'lifecycle_status' => LifecycleStatus::Assigned,
    ]);

    $clientToken = $clientUser->createToken('test')->plainTextToken;
    $providerToken = $providerUser->createToken('test')->plainTextToken;

    Auth::forgetGuards();

    $this->withToken($clientToken)
        ->getJson("/api/missions/{$mission->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.pairing_code', $mission->pairing_code);

    Auth::forgetGuards();

    $this->withToken($providerToken)
        ->getJson("/api/missions/{$mission->id}")
        ->assertSuccessful()
        ->assertJsonMissingPath('data.pairing_code');
});
