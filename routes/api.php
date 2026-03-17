<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActivityController;

Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

Route::post('/activity', [ActivityController::class, 'store']);
Route::get('/users/status', [ActivityController::class, 'allStatus']);
Route::get('/users/{id}/daily', [ActivityController::class, 'daily']);
Route::get('/users/{id}/weekly', [ActivityController::class, 'weekly']);
