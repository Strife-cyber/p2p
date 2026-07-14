<?php

namespace App\Services;

use App\Enums\ActivityStatus;
use App\Enums\ExecutionMode;
use App\Enums\LifecycleStatus;
use App\Models\Client;
use App\Models\Mission;
use App\Models\MissionApplication;
use App\Models\MissionFieldVerification;
use App\Models\Provider;
use App\Models\ProofFile;
use App\Support\GeoDistance;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MissionWorkflowService
{
    public function __construct(private EscrowService $escrow) {}

    public function createForClient(Client $client, array $attributes): Mission
    {
        return DB::transaction(function () use ($client, $attributes): Mission {
            $mission = Mission::query()->create([
                ...$attributes,
                'client_id' => $client->id,
                'lifecycle_status' => LifecycleStatus::Published,
                'pairing_code' => Mission::generatePairingCode(),
                'provider_id' => null,
            ]);

            MissionFieldVerification::query()->create([
                'mission_id' => $mission->id,
                'theoretical_latitude' => $attributes['latitude'],
                'theoretical_longitude' => $attributes['longitude'],
            ]);

            return $mission->load(['serviceCategory', 'fieldVerification']);
        });
    }

    /**
     * Classic mode: provider expresses interest while mission is published.
     */
    public function apply(Mission $mission, Provider $provider): MissionApplication
    {
        $this->assertStatus($mission, LifecycleStatus::Published);

        if ($mission->execution_mode !== ExecutionMode::Classic) {
            throw ValidationException::withMessages([
                'mission' => ['Applications are only accepted for classic missions.'],
            ]);
        }

        if ($mission->escrowLedger === null) {
            throw ValidationException::withMessages([
                'mission' => ['Client must lock escrow before providers can apply.'],
            ]);
        }

        return MissionApplication::query()->firstOrCreate(
            [
                'mission_id' => $mission->id,
                'provider_id' => $provider->id,
            ],
            ['status' => 'pending'],
        );
    }

    /**
     * Provider withdraws their application from a published mission.
     * Only works while the mission is still published (not yet assigned).
     */
    public function cancelApplication(Mission $mission, Provider $provider): MissionApplication
    {
        $this->assertStatus($mission, LifecycleStatus::Published);

        $application = MissionApplication::query()
            ->where('mission_id', $mission->id)
            ->where('provider_id', $provider->id)
            ->firstOrFail();

        if ($application->status !== 'pending') {
            throw ValidationException::withMessages([
                'application' => ['This application has already been processed and cannot be cancelled.'],
            ]);
        }

        $application->update(['status' => 'cancelled']);

        return $application->fresh();
    }

    /**
     * Client assigns a provider (classic: from applicants; VIP: direct pick).
     *
     * TODO(decision): VIP auto-matching by proximity/SRT — MVP requires client
     * to pass provider_id explicitly on this endpoint.
     */
    public function assign(Mission $mission, Provider $provider): Mission
    {
        $this->assertStatus($mission, LifecycleStatus::Published);

        if ($mission->escrowLedger === null) {
            throw ValidationException::withMessages([
                'mission' => ['Escrow must be locked before assigning a provider.'],
            ]);
        }

        if ($mission->execution_mode === ExecutionMode::Classic) {
            $hasApplication = $mission->applications()
                ->where('provider_id', $provider->id)
                ->exists();

            if (! $hasApplication) {
                throw ValidationException::withMessages([
                    'provider_id' => ['This provider has not applied to the mission.'],
                ]);
            }
        }

        // A provider cannot be assigned to more than one active mission at a time
        if ($this->providerHasActiveMission($provider)) {
            throw ValidationException::withMessages([
                'provider_id' => ['This provider already has an active (assigned or in-progress) mission.'],
            ]);
        }

        $mission->update([
            'provider_id' => $provider->id,
            'lifecycle_status' => LifecycleStatus::Assigned,
        ]);

        $provider->update(['activity_status' => ActivityStatus::OnMission]);

        $mission->applications()
            ->where('provider_id', $provider->id)
            ->update(['status' => 'accepted']);

        return $mission->fresh(['provider', 'client', 'escrowLedger']);
    }

    public function checkIn(
        Mission $mission,
        Provider $provider,
        float $latitude,
        float $longitude,
        string $selfieProofUrl,
    ): Mission {
        $this->assertAssignedProvider($mission, $provider);
        $this->assertStatus($mission, LifecycleStatus::Assigned);

        $verification = $mission->fieldVerification;

        if ($verification === null) {
            throw ValidationException::withMessages([
                'mission' => ['Mission field verification record is missing.'],
            ]);
        }

        $distance = GeoDistance::metersBetween(
            (float) $verification->theoretical_latitude,
            (float) $verification->theoretical_longitude,
            $latitude,
            $longitude,
        );

        $maxDistance = (float) config('p2p.check_in_max_distance_meters');

        if ($distance > $maxDistance) {
            throw ValidationException::withMessages([
                'latitude' => ["Check-in location is {$distance}m from the site (max {$maxDistance}m)."],
            ]);
        }

        DB::transaction(function () use ($mission, $verification, $latitude, $longitude, $selfieProofUrl): void {
            $verification->update([
                'checkin_latitude' => $latitude,
                'checkin_longitude' => $longitude,
                'checked_in_at' => now(),
            ]);

            ProofFile::query()->create([
                'mission_id' => $mission->id,
                'storage_url' => $selfieProofUrl,
                'captured_at' => now(),
            ]);

            $mission->update(['lifecycle_status' => LifecycleStatus::CheckInInProgress]);
        });

        return $mission->fresh(['fieldVerification', 'proofFiles']);
    }

    public function pair(Mission $mission, Provider $provider, string $submittedCode): Mission
    {
        $this->assertAssignedProvider($mission, $provider);
        $this->assertStatus($mission, LifecycleStatus::CheckInInProgress);

        if (! hash_equals($mission->pairing_code, strtoupper($submittedCode))) {
            throw ValidationException::withMessages([
                'pairing_code' => ['The pairing code is invalid.'],
            ]);
        }

        $mission->update(['lifecycle_status' => LifecycleStatus::InProgress]);

        return $mission->fresh();
    }

    /**
     * Provider declares work finished and submits after photos.
     *
     * TODO(decision): Require at least one "photo_before" proof from check-in/start?
     * MVP only requires photo_after URLs on completion.
     */
    public function complete(Mission $mission, Provider $provider, array $afterPhotoUrls): Mission
    {
        $this->assertAssignedProvider($mission, $provider);
        $this->assertStatus($mission, LifecycleStatus::InProgress);

        DB::transaction(function () use ($mission, $afterPhotoUrls): void {
            foreach ($afterPhotoUrls as $url) {
                ProofFile::query()->create([
                    'mission_id' => $mission->id,
                    'storage_url' => $url,
                    'captured_at' => now(),
                ]);
            }

            $mission->update([
                'lifecycle_status' => LifecycleStatus::Completed,
                'completed_at' => now(),
            ]);
        });

        return $mission->fresh(['proofFiles']);
    }

    public function validateCompletion(Mission $mission, Client $client): Mission
    {
        if ($mission->client_id !== $client->id) {
            throw ValidationException::withMessages([
                'mission' => ['Only the mission client can validate completion.'],
            ]);
        }

        $this->assertStatus($mission, LifecycleStatus::Completed);

        if ($mission->provider === null) {
            throw ValidationException::withMessages([
                'mission' => ['Mission has no assigned provider.'],
            ]);
        }

        return DB::transaction(function () use ($mission): Mission {
            $this->escrow->releaseSeventyPercent($mission, $mission->provider);

            $warrantyHours = (int) config('p2p.warranty_hours');

            $mission->update([
                'lifecycle_status' => LifecycleStatus::UnderWarranty,
                'warranty_expires_at' => now()->addHours($warrantyHours),
            ]);

            $mission->provider->update(['activity_status' => ActivityStatus::Available]);

            return $mission->fresh(['escrowLedger', 'provider']);
        });
    }

    /**
     * Close warranty period and release remaining escrow (30%).
     *
     * TODO(decision): Should this run automatically via a scheduled job when
     * warranty_expires_at passes, or only when the client explicitly confirms?
     * MVP allows client to call this after warranty_expires_at.
     */
    public function closeWarranty(Mission $mission, Client $client): Mission
    {
        if ($mission->client_id !== $client->id) {
            throw ValidationException::withMessages([
                'mission' => ['Only the mission client can close the warranty period.'],
            ]);
        }

        $this->assertStatus($mission, LifecycleStatus::UnderWarranty);

        if ($mission->warranty_expires_at === null || $mission->warranty_expires_at->isFuture()) {
            throw ValidationException::withMessages([
                'mission' => ['The warranty period has not expired yet.'],
            ]);
        }

        return DB::transaction(function () use ($mission): Mission {
            $this->escrow->releaseThirtyPercent($mission, $mission->provider);

            $mission->update(['lifecycle_status' => LifecycleStatus::Closed]);

            return $mission->fresh(['escrowLedger']);
        });
    }

    private function assertStatus(Mission $mission, LifecycleStatus $expected): void
    {
        if ($mission->lifecycle_status !== $expected) {
            throw ValidationException::withMessages([
                'mission' => ["Mission must be in \"{$expected->value}\" status."],
            ]);
        }
    }

    private function assertAssignedProvider(Mission $mission, Provider $provider): void
    {
        if ($mission->provider_id !== $provider->id) {
            throw ValidationException::withMessages([
                'mission' => ['You are not assigned to this mission.'],
            ]);
        }
    }

    /**
     * Check whether the provider already has an active (assigned or in-progress) mission,
     * which would violate the partial unique index uq_active_mission_provider.
     */
    private function providerHasActiveMission(Provider $provider): bool
    {
        return Mission::query()
            ->where('provider_id', $provider->id)
            ->whereIn('lifecycle_status', [
                LifecycleStatus::Assigned->value,
                LifecycleStatus::InProgress->value,
            ])
            ->exists();
    }
}
