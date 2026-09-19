<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VisitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public: login sales
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected: butuh token Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Info user yang login
    Route::get('/me', fn(Request $request) => response()->json($request->user()));

    // Kunjungan
    Route::get('/visits',                [VisitController::class, 'index']);
    Route::post('/visits',               [VisitController::class, 'store']);
    Route::get('/visits/{visit}',        [VisitController::class, 'show']);
    Route::post('/visits/{visit}/photo', [VisitController::class, 'uploadPhoto']);

    // Sync offline data
    Route::post('/sync', [VisitController::class, 'sync']);
});