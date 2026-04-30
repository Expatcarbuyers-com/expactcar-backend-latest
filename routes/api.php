<?php

use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\RedirectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ── Public API v1 ────────────────────────────────────────────────────────────

Route::prefix('v1')->middleware('throttle:60,1')->group(function () {

    // ── Car Catalog (cascade dropdown chain) ─────────────────
    Route::get('/preload',  [CarController::class, 'preload']); // years + makes for latest year in one shot
    Route::get('/years',    [CarController::class, 'years']);
    Route::get('/makes',    [CarController::class, 'makes']);
    Route::get('/models',           [CarController::class, 'models']);
    Route::get('/variants',         [CarController::class, 'variants']);
    Route::get('/variants-by-make', [CarController::class, 'variantsByMake']);
    Route::get('/catalog-sync',     [CarController::class, 'catalogSync']);

    // ── Search (Meilisearch with DB fallback) ─────────────────
    Route::get('/search', [CarController::class, 'search']);

    // ── Car/Make/Model page by slug ───────────────────────────
    Route::get('/cars/{slug}', [CarController::class, 'showBySlug']);

    // ── URL Redirects (for Next.js 301 handling) ─────────────
    Route::get('/redirects/{slug}', [RedirectController::class, 'show']);

    // ── Branches ─────────────────────────────────────────────
    Route::get('/branches', [BranchController::class, 'index']);

    // ── Blogs ─────────────────────────────────────────────────
    Route::get('/blogs',            [BlogController::class, 'index']);
    Route::get('/blogs/categories', [BlogController::class, 'categories']);
    Route::get('/blogs/{slug}',     [BlogController::class, 'show']);

    // ── Lead Submissions (increased to 60 per minute for testing) ─────────
    Route::middleware('throttle:60,1')->group(function () {
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::post('/contacts', [ContactController::class, 'store']);
    });
});

// ── Authenticated ────────────────────────────────────────────────────────────
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
