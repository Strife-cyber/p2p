<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalletResource;
use App\Http\Responses\ApiResponse;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Sandbox helper to fund a wallet without a payment gateway.
     *
     * TODO(decision): Remove or protect this endpoint in production — real funds
     * must only enter via verified Mobile Money IPN webhooks.
     */
    public function sandboxDeposit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $wallet = Wallet::query()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['current_balance' => 0],
        );

        $wallet->increment('current_balance', $validated['amount']);

        return ApiResponse::success(
            new WalletResource($wallet->fresh()),
            meta: ['note' => 'Sandbox deposit only — not real Mobile Money.'],
        );
    }

    public function show(Request $request): JsonResponse
    {
        $wallet = Wallet::query()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['current_balance' => 0],
        );

        return ApiResponse::resource(new WalletResource($wallet));
    }
}
