<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::prefix('/v1')->group(function () {
    Route::get('/user', fn() => request()->user())->middleware('auth:sanctum');

    // Orders routes
    Route::post('/orders', [OrderController::class, 'store']);
});
