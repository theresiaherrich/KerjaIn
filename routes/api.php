<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ApplicationController;


Route::post('/register', [AuthController::class,'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/refresh', [AuthController::class, 'login'])->name('refresh');
Route::post('/select-program', [UserController::class, 'selectProgram'])->name('selectProgram');

Route::middleware('auth:api')->get('/me', [AuthController::class, 'me']);
Route::middleware('auth:api')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:api')->get('/indexUser', [AuthController::class, 'index']);
Route::middleware('auth:api')->delete('/destroy/{id}', [AuthController::class, 'destroy']);

// Route::middleware('auth:api')->post('/refresh', [AuthController::class, 'refresh']);

Route::apiResource('/ads',App\Http\Controllers\Api\AdsController::class);
Route::apiResource('/applications',App\Http\Controllers\Api\ApplicationController::class);
Route::middleware('auth:api')->get('/historyApplication', [ApplicationController::class, 'userApplications']);
Route::apiResource('/articles',App\Http\Controllers\Api\ArticleController::class);
Route::apiResource('/communities',App\Http\Controllers\Api\CommunityController::class);
Route::apiResource('/companies',App\Http\Controllers\Api\CompanyController::class);
Route::apiResource('/disabilities',App\Http\Controllers\Api\DisabilityController::class);
Route::apiResource('/educations',App\Http\Controllers\Api\EducationController::class);
Route::apiResource('/experiences',App\Http\Controllers\Api\ExperienceController::class);
Route::apiResource('/jobsIn',App\Http\Controllers\Api\JobsInController::class);
Route::apiResource('/policies',App\Http\Controllers\Api\PolicyController::class);
Route::apiResource('/profiles',App\Http\Controllers\Api\ProfileController::class);
Route::apiResource('/programs',App\Http\Controllers\Api\ProgramController::class);
Route::apiResource('/stories',App\Http\Controllers\Api\StoryController::class);
Route::apiResource('/testimonies',App\Http\Controllers\Api\TestimonyController::class);
Route::apiResource('/types',App\Http\Controllers\Api\TypeController::class);


Route::middleware('auth:api')->group(function () {
    Route::post('/payments', [PaymentController::class, 'create']);
});
Route::middleware('auth:api')->get('/historyPayments', [PaymentController::class, 'history']);

Route::post('/midtrans/notification', [PaymentController::class, 'handleNotification']);



