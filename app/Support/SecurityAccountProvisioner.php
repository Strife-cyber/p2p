<?php

namespace App\Support;

use App\Models\SecurityAccount;
use App\Models\User;

class SecurityAccountProvisioner
{
    public function ensureForUser(
        User $user,
        ?string $deviceFingerprint = null,
        ?string $nationalIdHash = null,
    ): SecurityAccount {
        return SecurityAccount::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'real_phone' => $user->phone,
                'proxy_number' => $this->generateProxyNumber(),
                'device_fingerprint' => $deviceFingerprint ?? hash('sha256', 'provisioned:'.$user->id),
                'national_id_hash' => $nationalIdHash ?? hash('sha256', 'provisioned:'.$user->id),
            ],
        );
    }

    private function generateProxyNumber(): string
    {
        do {
            $proxyNumber = '+'.random_int(1000000000000, 9999999999999);
        } while (SecurityAccount::query()->where('proxy_number', $proxyNumber)->exists());

        return $proxyNumber;
    }
}
