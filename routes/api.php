<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FileUploadApiController;
use App\Http\Controllers\Api\UsersApiController;
use App\Http\Controllers\Api\Auth\CodeCheckController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\VerifyEmailController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('show_user_file/{slug}/{user_account}', [FileUploadApiController::class, 'show_user_file'])->name('show_user_file');
// Route::post('upload_file', [FileUploadApiController::class, 'upload_file'])->name('upload_file');
Route::post('upload_multiple_file', [FileUploadApiController::class, 'upload_multiple_file'])->name('upload_multiple_file');

Route::middleware(['cors'])->group(function () {
    Route::post('upload_file', [FileUploadApiController::class, 'upload_file'])->name('upload_file');
    
    Route::post('register_account', [UsersApiController::class, 'register_account'])->name('register_account');
    Route::post('authentication', [UsersApiController::class, 'authentication'])->name('authentication');
    Route::post('refresh_oauth_token', [UsersApiController::class, 'refresh_oauth_token'])->name('refresh_oauth_token');
    // Password reset routes
    Route::post('password/reset', ResetPasswordController::class);
    Route::post('password/email',  ForgotPasswordController::class);
    Route::post('password/code/check', CodeCheckController::class);
});
Route::middleware(['auth:api','verified'])->group(function () {
    Route::post('revoke_token', [UsersApiController::class, 'revoke_token'])->name('revoke_token');
    
    Route::get('/validate_token', function () {
        return response(['data' => 'Token is valid'], 200);
    });
    Route::get('load_all_files', [FileUploadApiController::class, 'load_all_files'])->name('load_all_files');
    Route::get('load_latest_file', [FileUploadApiController::class, 'load_latest_file'])->name('load_latest_file');
    Route::post('delete_all_file', [FileUploadApiController::class, 'delete_all_file'])->name('delete_all_file');
    // Resend link to verify email
    Route::post('/email/verify/resend', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    })->name('verification.send');
});

// Verify email
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

// // Resend link to verify email
// Route::post('/email/verify/resend', function (Request $request) {
//     $request->user()->sendEmailVerificationNotification();
//     return back()->with('message', 'Verification link sent!');
// })->middleware(['auth:api', 'throttle:6,1'])->name('verification.send');
