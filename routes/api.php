<?php

use App\Http\Controllers\api\AnnouncementController;
use App\Http\Controllers\Api\AppUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ResidentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'PONG_MTA is working'
    ], 200);
});



//Default user or administrator login and register
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);


//Mobile app login and register
Route::post('/appuser/login', [AppUserController::class, 'login']);
Route::post('/appuser/register', [AppUserController::class, 'register']);
Route::post('/appuser/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/appuser/resend-otp', [AuthController::class, 'resendOtp']);



// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('mobile-users', AppUserController::class);

    Route::apiResource('announcements', AnnouncementController::class);
});

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return response()->json([
        'user' => $request->user()
    ]);
});

Route::apiResource('residents', ResidentController::class);



Route::post('/send-to-one/{id}', [AppUserController::class, 'sendToOne']);
