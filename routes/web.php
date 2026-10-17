<?php

use App\Http\Controllers\User\LoginController;
use App\Http\Controllers\User\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.store');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.store');
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');
Route::view('/route-history', 'route-history')->name('route-history');
Route::view('/tracking', 'user.tracking')->middleware('auth')->name('tracking');
Route::view('stop-report', 'stop-report')->name('stop-report');
Route::view('zone-report', 'zone-report')->name('zone-report');
