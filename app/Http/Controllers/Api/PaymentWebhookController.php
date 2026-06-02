<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    /**
     * Mobile Money IPN endpoint (CinetPay / CamPay).
     *
     * TODO(decision): Implement gateway-specific signature verification and map
     * IPN payloads to wallet deposits + escrow lock confirmation. MVP does not
     * process external webhooks — clients fund wallets manually in sandbox.
     */
    public function __invoke(Request $request, string $gateway): JsonResponse
    {
        return ApiResponse::error('Payment webhook not implemented.', JsonResponse::HTTP_NOT_IMPLEMENTED);
    }
}
