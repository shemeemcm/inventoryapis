<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

// ── Public Routes ───────────────────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);

// ── Protected Routes ────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/products', [ProductController::class, 'index']);

    Route::post('/stock', [StockController::class, 'store']);

    Route::get('/warehouses/{warehouse}/report', [WarehouseController::class, 'report']);

});