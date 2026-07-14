<?php

return [

    /*
    |--------------------------------------------------------------------------
    | GPS check-in tolerance (meters)
    |--------------------------------------------------------------------------
    |
    | TODO(decision): Confirm acceptable distance between theoretical mission
    | coordinates and provider check-in. Urban GPS noise vs fraud prevention.
    |
    */
    'check_in_max_distance_meters' => (int) env('P2P_CHECK_IN_MAX_DISTANCE_METERS', 150),

    /*
    |--------------------------------------------------------------------------
    | Warranty period after client validation (hours)
    |--------------------------------------------------------------------------
    */
    'warranty_hours' => (int) env('P2P_WARRANTY_HOURS', 48),

    /*
    |--------------------------------------------------------------------------
    | Escrow split (provider payout)
    |--------------------------------------------------------------------------
    */
    'escrow_first_release_ratio' => 0.70,
    'escrow_second_release_ratio' => 0.30,

    /*
    |--------------------------------------------------------------------------
    | Platform commission on mission payment
    |--------------------------------------------------------------------------
    |
    | TODO(decision): What percentage does the platform retain from escrow?
    | MVP uses 0% — all locked funds go to the provider (70% + 30%).
    |
    */
    'platform_commission_ratio' => (float) env('P2P_PLATFORM_COMMISSION_RATIO', 0),

    /*
    |--------------------------------------------------------------------------
    | Payment gateway
    |--------------------------------------------------------------------------
    |
    | TODO(decision): CinetPay vs CamPay credentials, webhook signature scheme,
    | and whether escrow lock requires confirmed IPN before mission goes live.
    |
    */
    'payment_gateway' => env('P2P_PAYMENT_GATEWAY', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Provider withdrawal penalty (SRT points deducted)
    |--------------------------------------------------------------------------
    |
    | When a provider withdraws from an assigned mission, their SRT score
    | is reduced by this amount and their missions_without_dispute_count
    | resets to zero.
    |
    */
    'withdrawal_srt_penalty' => (float) env('P2P_WITHDRAWAL_SRT_PENALTY', 3.5),

    /*
    |--------------------------------------------------------------------------
    | SRT bonus for successful mission completion
    |--------------------------------------------------------------------------
    |
    | When a provider completes a mission and the client validates it, their
    | SRT score increases by this amount and missions_without_dispute_count
    | increments by 1.
    |
    */
    'completion_srt_bonus' => (float) env('P2P_COMPLETION_SRT_BONUS', 2.0),

];
