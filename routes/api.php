<?php

use App\Http\Controllers\api\AnnouncementController;
use App\Http\Controllers\Api\AppUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\ResidentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'PONG_MTA is working advance'
    ], 200);
});



//Default user or administrator login and register
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

// FOR MOBILE APP USERS
//Mobile app login and register
Route::post('/appuser/login', [AppUserController::class, 'login']);
Route::post('/appuser/register', [AppUserController::class, 'register']);
Route::post('/appuser/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/appuser/resend-otp', [AuthController::class, 'resendOtp']);


Route::middleware('auth:sanctum')->get('/me/granted-status', [AuthController::class, 'myGrantedStatus']);


// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    //Logout
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::apiResource('mobile-users', AppUserController::class);
    Route::apiResource('announcements', AnnouncementController::class);


    //Certificates
    Route::post('/certificates', [CertificateController::class, 'store']);
    Route::get('/certificates', [CertificateController::class, 'index']);
    Route::delete('/certificates/{id}', [CertificateController::class, 'destroy']);
    Route::patch('/certificates/{id}/status', [CertificateController::class, 'updateStatus']);
});

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return response()->json([
        'user' => $request->user()
    ]);
});

Route::apiResource('residents', ResidentController::class);



Route::post('/send-to-one/{id}', [AppUserController::class, 'sendToOne']);
