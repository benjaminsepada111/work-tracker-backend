<?php

use App\Http\Controllers\ActivityController;
use Illuminate\Support\Facades\Route;

Route::post('/activity', [ActivityController::class, 'store']);
Route::get('/users/status', [ActivityController::class, 'allStatus']);
Route::get('/users/{id}/daily', [ActivityController::class, 'daily']);
Route::get('/users/{id}/weekly', [ActivityController::class, 'weekly']);