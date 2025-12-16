<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\ClientMenuController;
use App\Http\Controllers\Api\ClientCartController;
use App\Http\Controllers\Api\ClientOrderController;
use App\Http\Controllers\Api\CookOrderController;
use App\Http\Controllers\Api\WaiterOrderController;

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

Route::post('/order', [ClientOrderController::class, 'store']);
Route::get('/order/{orderId}', [ClientOrderController::class, 'status'])
    ->middleware('order.token'); // middleware для проверки токена
Route::post('/order/{orderId}/pay', [ClientOrderController::class, 'pay'])
    ->middleware('order.token');

Route::middleware(['auth:sanctum', 'role:cook'])->prefix('staff/cook/order')->group(function () {
    Route::get('/', [CookOrderController::class, 'index']);
    Route::get('/owns', [CookOrderController::class, 'owns']);
    Route::post('/{id}/get', [CookOrderController::class, 'get']);
    Route::post('/{id}/ready', [CookOrderController::class, 'ready']);
});

Route::middleware(['auth:sanctum', 'role:waiter'])->prefix('staff/waiter/order')->group(function () {
    Route::get('/', [WaiterOrderController::class, 'index']);
    Route::post('/{id}/get', [WaiterOrderController::class, 'get']);
    Route::post('/{id}/served', [WaiterOrderController::class, 'served']);
});
