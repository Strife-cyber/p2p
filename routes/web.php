<?php

use App\Http\Controllers\ApiDocumentationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/documentation', [ApiDocumentationController::class, 'ui'])
    ->name('api.docs.ui');

Route::get('/api/docs/openapi.yaml', [ApiDocumentationController::class, 'spec'])
    ->name('api.docs.spec');
