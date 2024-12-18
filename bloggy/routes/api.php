<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\CommentController;

Route::middleware('guest')->group(function () {
    // Token
    Route::post('login', [TokenController::class, 'login']);
    // Statuses
    Route::apiResource('statuses', StatusController::class);
    // Posts
    Route::apiResource('posts', PostController::class)
        ->only('index', 'show');
});

Route::middleware('auth:sanctum')->group(function () {
    // Token
    Route::get('user', [TokenController::class, 'user']);
    Route::post('logout', [TokenController::class, 'logout']);
    // Posts
    Route::apiResource('posts', PostController::class)
        ->only('store', 'update', 'destroy');
    // Comments (nested routes)
    Route::apiResource('posts.comments', CommentController::class);
});