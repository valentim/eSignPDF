<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Presentation\Controllers\Auth\AuthController;
use App\Presentation\Controllers\Document\DocumentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/documents', [DocumentController::class, 'index']);
    Route::post('/documents', [DocumentController::class, 'upload']);
    Route::delete('/documents/{document:uuid}', [DocumentController::class, 'delete']);
    Route::post('/documents/{document:uuid}/sign', [DocumentController::class, 'sign']);
    Route::get('/documents/{document:uuid}/download', [DocumentController::class, 'download']);
});
