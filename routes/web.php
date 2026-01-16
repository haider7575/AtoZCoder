<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StaffController;

Route::get('/', function () {
return view('login');
})->name('login');

Route::post('/login', [AuthController::class, 'webLogin']);
Route::post('/logout', [AuthController::class, 'webLogout'])->name('logout');

Route::middleware('auth')->group(function () {
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Products
Route::resource('products', ProductController::class)->except(['create', 'edit', 'show']); // Index handled by dashboard, creating via modal

// Staff
Route::resource('staff', StaffController::class)->except(['create', 'edit', 'show']);

// Orders
Route::resource('orders', OrderController::class)->only(['store']);
Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
});