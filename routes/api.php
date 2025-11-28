<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::resource('/products', App\Http\Controllers\Api\ProductController::class)
    ->only(['index', 'show', 'store', 'update', 'destroy'])
    ->middleware(['auth:sanctum', 'role:admin']);

Route::resource('/staff', App\Http\Controllers\Api\StaffController::class)
    ->only(['index', 'show', 'store', 'update', 'destroy'])
    ->middleware(['auth:sanctum', 'role:admin']);

