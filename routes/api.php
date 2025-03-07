<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/register', [AuthController::class,'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
// Route::post('/logout', [AuthController::class,'logout']);
// Route::post('/refresh', [AuthController::class,'refresh']);


Route::middleware('auth:api')->get('/me', [AuthController::class, 'me']);
Route::middleware('auth:api')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:api')->post('/refresh', [AuthController::class, 'refresh']);


Route::apiResource('/lowongans',App\Http\Controllers\Api\LowonganController::class);
Route::apiResource('/ads',App\Http\Controllers\Api\AdsController::class);
// Route::middleware('api')->apiResource('/ads', App\Http\Controllers\Api\AdsController::class);
// Route::apiResource('/ads', AdsController::class);
Route::apiResource('/applications',App\Http\Controllers\Api\ApplicationController::class);
Route::apiResource('/articles',App\Http\Controllers\Api\ArticleController::class);
Route::apiResource('/communities',App\Http\Controllers\Api\CommunityController::class);
Route::apiResource('/companies',App\Http\Controllers\Api\CompanyController::class);
Route::apiResource('/disabilities',App\Http\Controllers\Api\DisabilityController::class);
Route::apiResource('/educations',App\Http\Controllers\Api\EducationController::class);
Route::apiResource('/experiences',App\Http\Controllers\Api\ExperienceController::class);
Route::apiResource('/jobsIn',App\Http\Controllers\Api\jobsInController::class);
Route::apiResource('/policies',App\Http\Controllers\Api\PolicyController::class);
Route::apiResource('/profiles',App\Http\Controllers\Api\ProfileController::class);
Route::apiResource('/programs',App\Http\Controllers\Api\ProgramController::class);
Route::apiResource('/stories',App\Http\Controllers\Api\StoryController::class);
Route::apiResource('/testimonies',App\Http\Controllers\Api\TestimonyController::class);
Route::apiResource('/types',App\Http\Controllers\Api\TypeController::class);

