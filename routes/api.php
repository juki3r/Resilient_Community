<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AppUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlotterController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\EvacuationCenterController;
use App\Http\Controllers\Api\EventsController;
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\OfficialController;
use App\Http\Controllers\Api\OrdinanceController;
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
Route::post('/appuser/verify-otp', [AppUserController::class, 'verifyOtp']);
Route::post('/appuser/send-forgot-otp', [AppUserController::class, 'sendForgotOtp']);
Route::post('/appuser/verify-reset-otp', [AppUserController::class, 'verifyResetOtp']);
Route::post('/appuser/reset-password', [AppUserController::class, 'resetPassword']);
Route::post('/appuser/save-fcm-token', [AppUserController::class, 'saveFcmToken']);
Route::post('/appuser/send-otp', [AppUserController::class, 'sendOtp']);




Route::middleware('auth:sanctum')->get('/me/granted-status', [AuthController::class, 'myGrantedStatus']);


// Protected routes
Route::middleware('auth:sanctum')->group(function () {


    //================================== Mobile App ============================================
    //Logout
    Route::post('/appuser/logout', [AppUserController::class, 'logout']);


    //Officials
    Route::get('/appuser/officials', [OfficialController::class, 'index_appuser']);

    //Ordinance
    Route::get('/appuser/ordinances', [OrdinanceController::class, 'index_appuser']);

    //News
    Route::get('/appuser/news', [NewsController::class, 'index_appuser']);
    Route::post('/appuser/news/{id}/view', [NewsController::class, 'markViewed']);
    Route::get('/appuser/unread-news', [NewsController::class, 'unreadNews']);


    //Certifications
    Route::post('/appuser/requests', [CertificateController::class, 'store_appuser']);
    Route::get('/appuser/my-requests', [CertificateController::class, 'index_appuser']);


    //Blotters
    Route::get('/appuser/blotters', [BlotterController::class, 'index_appuser']);



    //=========================== WEB =======================================================

    //Logout
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    // Route::apiResource('mobile-users', AppUserController::class);
    Route::apiResource('announcements', AnnouncementController::class);


    //Residents
    Route::get('/residents', [ResidentController::class, 'index']);
    Route::post('/residents', [ResidentController::class, 'store']);
    Route::get('/residents/{id}', [ResidentController::class, 'show']);
    Route::put('/residents/{id}', [ResidentController::class, 'update']);
    Route::delete('/residents/{id}', [ResidentController::class, 'destroy']);


    //Certificates
    Route::post('/certificates', [CertificateController::class, 'store']);
    Route::get('/certificates', [CertificateController::class, 'index']);
    Route::delete('/certificates/{id}', [CertificateController::class, 'destroy']);
    Route::patch('/certificates/{id}/status', [CertificateController::class, 'updateStatus']);




    Route::prefix('blotters')->group(function () {

        Route::get('/', [BlotterController::class, 'index']);
        Route::post('/', [BlotterController::class, 'store']);
        Route::get('/{id}', [BlotterController::class, 'show']);
        Route::put('/{id}', [BlotterController::class, 'update']);
        Route::delete('/{id}', [BlotterController::class, 'destroy']);

        // Soft delete extras
        Route::get('/trashed/all', [BlotterController::class, 'trashed']);
        Route::post('/restore/{id}', [BlotterController::class, 'restore']);
        Route::delete('/force/{id}', [BlotterController::class, 'forceDelete']);
    });



    Route::get('/news', [NewsController::class, 'index']);
    Route::post('/news', [NewsController::class, 'store']);
    Route::get('/news/{id}', [NewsController::class, 'show']);
    Route::put('/news/{id}', [NewsController::class, 'update']);
    Route::delete('/news/{id}', [NewsController::class, 'destroy']);


    Route::get('/ordinances', [OrdinanceController::class, 'index']);
    Route::post('/ordinances', [OrdinanceController::class, 'store']);
    Route::get('/ordinances/{id}', [OrdinanceController::class, 'show']);
    Route::put('/ordinances/{id}', [OrdinanceController::class, 'update']);
    Route::delete('/ordinances/{id}', [OrdinanceController::class, 'destroy']);

    Route::apiResource('incidents', IncidentController::class);

    Route::apiResource('officials', OfficialController::class);

    Route::apiResource('events', EventsController::class);

    // Centers
    Route::get('/evacuation-centers', [EvacuationCenterController::class, 'index']);
    Route::post('/evacuation-centers', [EvacuationCenterController::class, 'store']);
    Route::put('/evacuation-centers/{id}', [EvacuationCenterController::class, 'update']);
    Route::delete('/evacuation-centers/{id}', [EvacuationCenterController::class, 'destroy']);

    Route::post('/save-web-token', [AuthController::class, 'saveWebToken']);
});

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return response()->json([
        'user' => $request->user()
    ]);
});

Route::middleware('auth:sanctum')->get('/appuser/me', function (Request $request) {
    return response()->json([
        'user' => $request->user()
    ]);
});





Route::post('/send-to-one/{id}', [AppUserController::class, 'sendToOne']);
