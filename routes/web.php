<?php

use Illuminate\Support\Facades\Route;
use App\Presentation\Controllers\Auth\SocialiteController;
use App\Presentation\Controllers\Documents\DocumentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [SocialiteController::class, 'handleGoogleCallback']);
Route::get('/documents/{document:uuid}/callback', [DocumentController::class, 'callback']);


Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');