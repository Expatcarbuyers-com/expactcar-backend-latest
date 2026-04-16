<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\ContactController;

Route::prefix('v1')->group(function () {
    // Car Metadata
    Route::get('/years', [CarController::class, 'years']);
    Route::get('/makes', [CarController::class, 'makes']);
    Route::get('/models', [CarController::class, 'models']);
    Route::get('/variants', [CarController::class, 'variants']);
    Route::get('/search', [CarController::class, 'search']);
    Route::get('/slug/{slug}', [CarController::class, 'showBySlug']);
    
    // Bookings
    Route::post('/bookings', [BookingController::class, 'store']);

    // Contacts
    Route::post('/contacts', [ContactController::class, 'store']);

    // Branches
    Route::get('/branches', function() {
        return response()->json([
            'success' => true,
            'data' => \App\Models\Branch::where('is_active', true)->get()
        ]);
    });

    // Blogs
    Route::get('/blogs', [BlogController::class, 'index']);
    Route::get('/blogs/{slug}', [BlogController::class, 'show']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
