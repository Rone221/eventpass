<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\TicketValidationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
|  API REST v1 — application mobile de scan de billets
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Authentification du scanner (émission d'un token Sanctum)
    Route::post('/auth/token', [ApiAuthController::class, 'token'])->name('api.auth.token');

    // Endpoints protégés par token Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', fn (\Illuminate\Http\Request $request) => $request->user());
        Route::post('/auth/logout', [ApiAuthController::class, 'logout'])->name('api.auth.logout');

        // Validation d'un billet scanné
        Route::post('/tickets/validate', [TicketValidationController::class, 'validate'])
            ->name('api.tickets.validate');
    });
});
