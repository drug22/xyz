<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// API v1 - Orders Management (Protected)
Route::middleware('api.token:orders')->prefix('orders')->group(function () {
    Route::post('create', [App\Http\Controllers\Api\OrderController::class, 'create']);
    Route::get('{order}/show', [App\Http\Controllers\Api\OrderController::class, 'show']);
    Route::get('{order}/status', [App\Http\Controllers\Api\OrderController::class, 'status']);
    Route::post('{order}/payment', [App\Http\Controllers\Api\OrderController::class, 'processPayment']);
});

// API v1 - Packages (Protected)
Route::middleware('api.token:packages')->prefix('packages')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\PackageController::class, 'index']);
    Route::get('{package}', [App\Http\Controllers\Api\PackageController::class, 'show']);
});

// API v1 - Tax & VAT Calculations (Protected)
Route::middleware('api.token:tax')->prefix('tax')->group(function () {
    Route::post('calculate', [App\Http\Controllers\Api\TaxController::class, 'calculate']);
    Route::post('validate-vat', [App\Http\Controllers\Api\TaxController::class, 'validateVat']);
    Route::get('countries', [App\Http\Controllers\Api\TaxController::class, 'countries']);
});

// Health Check (Unprotected)
Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});
