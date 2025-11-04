<?php

use App\Http\Controllers\GoogleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', function () {
    return view('welcome');
});

//RUTA DE GOOGLE
Route::middleware('web')->group(function () {
    Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle']);
    Route::get('/logingoogle', [GoogleController::class, 'handleGoogleCallback']);
});