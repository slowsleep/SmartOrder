<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\TableController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';

Route::get('menu', function () {
    return Inertia::render('Client/Menu/Menu');
})->name('client.menu');

Route::get('cart', function () {
    return Inertia::render('Client/Cart/Cart');
})->name('client.cart');

Route::get('order/{orderId}', function () {
    return Inertia::render('Client/Order/Order');
})->middleware('order.token')->name('client.order.status');

Route::get('/table/{qr_token}', [TableController::class, 'init'])->name('table.init');

Route::middleware(['auth', 'verified', 'role:cook'])->group(function () {
    Route::get('kitchen', function () {
        return Inertia::render('Kitchen/KitchenDashboard');
    })->name('cook-kitchen');

    Route::get('kitchen/orders', function () {
        return Inertia::render('Kitchen/Orders');
    })->name('cook-orders');

    Route::get('kitchen/personal-orders', function () {
        return Inertia::render('Kitchen/PersonalOrders', ['user_id' => Auth::id()]);
    })->name('cook-personal-orders');
});

Route::middleware(['auth', 'verified', 'role:waiter'])->group(function () {
    Route::get('service', function () {
        return Inertia::render('Service/ServiceDashboard');
    })->name('waiter-service');

    Route::get('service/orders', function () {
        return Inertia::render('Service/Orders');
    })->name('waiter-general-orders');

    Route::get('service/personal-orders', function () {
        return Inertia::render('Service/PersonalOrders', ['user_id' => Auth::id()]);
    })->name('waiter-personal-orders');
});
