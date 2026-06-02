<?php

use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

it('does not expose database internals on api server errors', function () {
    Route::middleware('api')->get('/api/__test/boom', function () {
        throw new Exception('secret internal failure');
    });

    $response = $this->getJson('/api/__test/boom');

    $response
        ->assertStatus(500)
        ->assertJsonPath('message', 'A server error occurred. Please try again later.')
        ->assertJsonMissingPath('exception')
        ->assertJsonMissingPath('trace')
        ->assertJsonMissingPath('file');

    expect($response->getContent())->not->toContain('secret internal failure');
});

it('creates a client profile when the user has no security account yet', function () {
    $user = User::factory()->create([
        'phone' => PhoneNumber::normalize('+237670000050'),
        'password' => Hash::make('password'),
    ]);

    $token = $user->createToken('mobile')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/client/profile', ['client_type' => 'individual'])
        ->assertCreated()
        ->assertJsonPath('data.client_type', 'individual');

    $this->assertDatabaseHas('security_accounts', ['user_id' => $user->id]);
    $this->assertDatabaseHas('clients', ['security_account_id' => $user->id]);
});
