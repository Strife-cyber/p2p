<?php

namespace App\Support;

use App\Models\Client;
use App\Models\Provider;
use App\Models\User;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class ActorProfile
{
    public function __construct(public User $user) {}

    public function client(): Client
    {
        $client = Client::query()->where('security_account_id', $this->user->id)->first();

        if ($client === null) {
            $this->abortMissingProfile('client');
        }

        return $client;
    }

    public function provider(): Provider
    {
        $provider = Provider::query()->where('security_account_id', $this->user->id)->first();

        if ($provider === null) {
            $this->abortMissingProfile('provider');
        }

        return $provider;
    }

    public function clientOrNull(): ?Client
    {
        return Client::query()->where('security_account_id', $this->user->id)->first();
    }

    public function providerOrNull(): ?Provider
    {
        return Provider::query()->where('security_account_id', $this->user->id)->first();
    }

    private function abortMissingProfile(string $role): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                "You must complete {$role} onboarding before using this endpoint.",
                JsonResponse::HTTP_FORBIDDEN,
            ),
        );
    }
}
