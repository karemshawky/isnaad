<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::get('/user', fn() => request()->user())->middleware('auth:sanctum');

Route::post('/orders', [OrderController::class, 'store']);
