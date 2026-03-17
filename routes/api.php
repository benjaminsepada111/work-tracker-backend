<?php

use App\Http\Controllers\ActivityController;
use Illuminate\Support\Facades\Route;

// No authentication required for these routes
Route::withoutMiddleware(['auth'])->group(function () {
    
    // Receive a ping from the Python tracker
    Route::post('/activity', [ActivityController::class, 'store']);

    // Get all users current status (Flutter dashboard)
    Route::get('/users/status', [ActivityController::class, 'allStatus']);

    // Get one user logs
    Route::get('/users/{id}/daily',  [ActivityController::class, 'daily']);
    Route::get('/users/{id}/weekly', [ActivityController::class, 'weekly']);
    
});