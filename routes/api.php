<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
// 公開路由
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');

// 受保護路由
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

});
