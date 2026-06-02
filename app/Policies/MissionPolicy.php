<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\Mission;
use App\Models\Provider;
use App\Models\User;

class MissionPolicy
{
    public function view(User $user, Mission $mission): bool
    {
        return $this->isClient($user, $mission) || $this->isProvider($user, $mission);
    }

    public function update(User $user, Mission $mission): bool
    {
        return $this->isClient($user, $mission);
    }

    public function assign(User $user, Mission $mission): bool
    {
        return $this->isClient($user, $mission);
    }

    public function apply(User $user, Mission $mission): bool
    {
        return Provider::query()->where('security_account_id', $user->id)->exists();
    }

    public function checkIn(User $user, Mission $mission): bool
    {
        return $this->isProvider($user, $mission);
    }

    public function pair(User $user, Mission $mission): bool
    {
        return $this->isProvider($user, $mission);
    }

    public function complete(User $user, Mission $mission): bool
    {
        return $this->isProvider($user, $mission);
    }

    public function validate(User $user, Mission $mission): bool
    {
        return $this->isClient($user, $mission);
    }

    public function closeWarranty(User $user, Mission $mission): bool
    {
        return $this->isClient($user, $mission);
    }

    private function isClient(User $user, Mission $mission): bool
    {
        return Client::query()
            ->where('security_account_id', $user->id)
            ->whereKey($mission->client_id)
            ->exists();
    }

    private function isProvider(User $user, Mission $mission): bool
    {
        if ($mission->provider_id === null) {
            return false;
        }

        return Provider::query()
            ->where('security_account_id', $user->id)
            ->whereKey($mission->provider_id)
            ->exists();
    }
}
