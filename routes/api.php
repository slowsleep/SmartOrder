<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\ClientMenuController;
use App\Http\Controllers\Api\ClientCartController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::resource('/products', ProductController::class)
    ->only(['index', 'show', 'store', 'update', 'destroy'])
    ->middleware(['auth:sanctum', 'role:admin']);

Route::resource('/staff', StaffController::class)
    ->only(['index', 'show', 'store', 'update', 'destroy'])
    ->middleware(['auth:sanctum', 'role:admin']);

Route::resource('/menu', ClientMenuController::class)
    ->only(['index', 'show']);

Route::prefix('cart')->group(function () {
    Route::get('/', [ClientCartController::class, 'index']);
    Route::post('/add', [ClientCartController::class, 'add']);
    Route::post('/decrease', [ClientCartController::class, 'decrease']);
    Route::post('/remove', [ClientCartController::class, 'remove']);
    Route::post('/clear', [ClientCartController::class, 'clear']);
});
