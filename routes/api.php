<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FileUploadApiController;
use App\Http\Controllers\Api\UsersApiController;
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
});
Route::middleware('auth:api')->group(function () {
    Route::post('revoke_token', [UsersApiController::class, 'revoke_token'])->name('revoke_token');
    
    Route::get('/validate_token', function () {
        return response(['data' => 'Token is valid'], 200);
    });
    Route::get('load_all_files', [FileUploadApiController::class, 'load_all_files'])->name('load_all_files');
});
