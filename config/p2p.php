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

];
