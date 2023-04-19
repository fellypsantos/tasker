<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;


Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('me', [AuthController::class, 'getMe']);

    Route::apiResource('tasks', TaskController::class)
        ->only(['index', 'store'])
        ->middleware('auth:sanctum');

    Route::apiResource('tasks', TaskController::class)
        ->except(['index', 'store'])
        ->middleware('check.task.ownership');
});
