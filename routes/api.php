<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientProfileController;
use App\Http\Controllers\Api\MissionController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\ProviderProfileController;
use App\Http\Controllers\Api\ServiceCategoryController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});

Route::post('/webhooks/payments/{gateway}', PaymentWebhookController::class);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/service-categories', [ServiceCategoryController::class, 'index']);

    Route::post('/client/profile', [ClientProfileController::class, 'store']);
    Route::get('/client/profile', [ClientProfileController::class, 'show']);

    Route::post('/provider/profile', [ProviderProfileController::class, 'store']);
    Route::get('/provider/profile', [ProviderProfileController::class, 'show']);

    Route::get('/wallet', [WalletController::class, 'show']);
    Route::post('/wallet/sandbox-deposit', [WalletController::class, 'sandboxDeposit']);

    Route::get('/missions', [MissionController::class, 'index']);
    Route::post('/missions', [MissionController::class, 'store']);
    Route::get('/missions/{mission}', [MissionController::class, 'show']);
    Route::post('/missions/{mission}/escrow', [MissionController::class, 'lockEscrow']);
    Route::post('/missions/{mission}/applications', [MissionController::class, 'apply']);
    Route::post('/missions/{mission}/assign', [MissionController::class, 'assign']);
    Route::post('/missions/{mission}/check-in', [MissionController::class, 'checkIn']);
    Route::post('/missions/{mission}/pair', [MissionController::class, 'pair']);
    Route::post('/missions/{mission}/complete', [MissionController::class, 'complete']);
    Route::post('/missions/{mission}/validate', [MissionController::class, 'validateCompletion']);
    Route::post('/missions/{mission}/warranty/close', [MissionController::class, 'closeWarranty']);
});
