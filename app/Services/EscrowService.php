<?php

namespace App\Services;

use App\Enums\EscrowStatus;
use App\Enums\LifecycleStatus;
use App\Enums\TransactionType;
use App\Models\Client;
use App\Models\EscrowLedger;
use App\Models\FinancialTransaction;
use App\Models\Mission;
use App\Models\Provider;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EscrowService
{
    /**
     * Lock client funds for a published mission (MVP: internal wallet debit only).
     *
     * Auto-generates a transaction reference if none provided — one-click lock.
     * Only asks the client to fund their wallet if the balance is insufficient.
     */
    public function lockForMission(Mission $mission, Client $client, ?string $paymentReference = null): EscrowLedger
    {
        if ($mission->lifecycle_status !== LifecycleStatus::Published) {
            throw ValidationException::withMessages([
                'mission' => ['Escrow can only be locked for published missions.'],
            ]);
        }

        if ($mission->escrowLedger !== null) {
            throw ValidationException::withMessages([
                'mission' => ['Escrow is already locked for this mission.'],
            ]);
        }

        $reference = $paymentReference ?? 'ESC-' . strtoupper(Str::random(12));

        return DB::transaction(function () use ($mission, $client, $reference): EscrowLedger {
            $wallet = $this->walletForUser($client->security_account_id);
            $amount = (float) $mission->estimated_price;

            if ((float) $wallet->current_balance < $amount) {
                throw ValidationException::withMessages([
                    'wallet' => ['Insufficient wallet balance to lock escrow.'],
                ]);
            }

            $wallet->decrement('current_balance', $amount);

            FinancialTransaction::query()->create([
                'wallet_id' => $wallet->id,
                'amount' => -$amount,
                'transaction_type' => TransactionType::MissionPayment,
                'external_reference' => $reference,
                'created_at' => now(),
            ]);

            return EscrowLedger::query()->create([
                'mission_id' => $mission->id,
                'total_amount' => $amount,
                'escrow_status' => EscrowStatus::Blocked,
                'transaction_reference' => $reference,
                'locked_at' => now(),
            ]);
        });
    }

    public function releaseSeventyPercent(Mission $mission, Provider $provider): EscrowLedger
    {
        return $this->releasePortion($mission, $provider, EscrowStatus::SeventyPercentReleased, 'escrow_first_release_ratio');
    }

    public function releaseThirtyPercent(Mission $mission, Provider $provider): EscrowLedger
    {
        return $this->releasePortion($mission, $provider, EscrowStatus::ThirtyPercentReleased, 'escrow_second_release_ratio', finalize: true);
    }

    private function releasePortion(
        Mission $mission,
        Provider $provider,
        EscrowStatus $targetStatus,
        string $ratioConfigKey,
        bool $finalize = false,
    ): EscrowLedger {
        $ledger = $mission->escrowLedger;

        if ($ledger === null) {
            throw ValidationException::withMessages([
                'mission' => ['No escrow ledger exists for this mission.'],
            ]);
        }

        $expectedPrior = match ($targetStatus) {
            EscrowStatus::SeventyPercentReleased => EscrowStatus::Blocked,
            EscrowStatus::ThirtyPercentReleased => EscrowStatus::SeventyPercentReleased,
            default => null,
        };

        if ($ledger->escrow_status !== $expectedPrior) {
            throw ValidationException::withMessages([
                'escrow' => ['Escrow is not in the correct state for this release.'],
            ]);
        }

        return DB::transaction(function () use ($mission, $provider, $ledger, $targetStatus, $ratioConfigKey, $finalize): EscrowLedger {
            $ratio = (float) config("p2p.{$ratioConfigKey}");
            $commissionRatio = (float) config('p2p.platform_commission_ratio');
            $gross = (float) $ledger->total_amount * $ratio;
            $payout = round($gross * (1 - $commissionRatio), 2);

            $wallet = $this->walletForUser($provider->security_account_id);
            $wallet->increment('current_balance', $payout);

            FinancialTransaction::query()->create([
                'wallet_id' => $wallet->id,
                'amount' => $payout,
                'transaction_type' => TransactionType::MissionPayment,
                'external_reference' => "mission:{$mission->id}:{$targetStatus->value}",
                'created_at' => now(),
            ]);

            $ledger->update([
                'escrow_status' => $targetStatus,
                'released_at' => $finalize ? now() : $ledger->released_at,
            ]);

            return $ledger->fresh();
        });
    }

    private function walletForUser(string $userId): Wallet
    {
        return Wallet::query()->firstOrCreate(
            ['user_id' => $userId],
            ['current_balance' => 0],
        );
    }
}
