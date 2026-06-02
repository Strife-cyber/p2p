<?php

use App\Models\SecurityAccount;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

it('registers a user with phone number and returns sanctum token', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Jean Dupont',
        'phone' => '237670000099',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'device_name' => 'iphone-15',
        'device_fingerprint' => hash('sha256', 'device-abc'),
        'national_id_hash' => hash('sha256', 'cni-abc'),
    ]);

    $response
        ->assertCreated()
        ->assertJsonStructure(['data' => ['id', 'name', 'phone'], 'meta' => ['token']]);

    expect($response->json('data.phone'))->toBe('+237670000099');

    $this->assertDatabaseHas('users', [
        'phone' => '+237670000099',
    ]);

    $this->assertDatabaseHas('security_accounts', [
        'real_phone' => '+237670000099',
    ]);
});

it('logs in with phone number and password', function () {
    $user = User::factory()->create([
        'phone' => PhoneNumber::normalize('+237670000010'),
        'password' => Hash::make('password123'),
    ]);

    SecurityAccount::factory()->for($user)->create();

    $response = $this->postJson('/api/auth/login', [
        'phone' => '237670000010',
        'password' => 'password123',
        'device_name' => 'android-14',
    ]);

    $response
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['id', 'name', 'phone'], 'meta' => ['token']]);
});

it('rejects invalid phone credentials', function () {
    User::factory()->create([
        'phone' => PhoneNumber::normalize('+237670000011'),
        'password' => Hash::make('password123'),
    ]);

    $this->postJson('/api/auth/login', [
        'phone' => '237670000011',
        'password' => 'wrong-password',
        'device_name' => 'android-14',
    ])->assertUnprocessable();
});

it('returns the authenticated user profile', function () {
    $user = User::factory()->create([
        'phone' => PhoneNumber::normalize('+237670000012'),
    ]);

    $token = $user->createToken('test-device')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/user')
        ->assertSuccessful()
        ->assertJsonPath('data.phone', '+237670000012');
});

it('revokes the current access token on logout', function () {
    $user = User::factory()->create([
        'phone' => PhoneNumber::normalize('+237670000013'),
    ]);

    $token = $user->createToken('test-device')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/auth/logout')
        ->assertSuccessful();

    expect($user->fresh()->tokens)->toHaveCount(0);

    Auth::forgetGuards();

    $this->withToken($token)
        ->getJson('/api/user')
        ->assertUnauthorized();
});
