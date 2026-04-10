<?php

use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\GoogleFormController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $hasToken = file_exists(storage_path('app/private/google-token.json'));

    return view('welcome', compact('hasToken'));
});

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.auth');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');

Route::post('/generate', [GoogleFormController::class, 'store'])
    ->name('google-form.generate');
