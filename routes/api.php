<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CompareController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('products', ProductController::class);
Route::apiResource('categories', CategoryController::class);

Route::get('products/top10', ProductController::class, 'top10Products');

Route::prefix('compare')->group(function() {
    Route::get('/', [CompareController::class, 'index']);
    Route::post('add', [CompareController::class, 'add']);
    Route::delete('{id}', [CompareController::class, 'remove']);
    Route::delete('clear', [CompareController::class, 'clear']);
});