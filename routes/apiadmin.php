<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\AuthController;
use App\Http\Controllers\Dashboard\BlogController;
use App\Http\Controllers\Dashboard\CategoryController;


Route::post('/login',[AuthController::class,'login']);

Route::group(["middleware" => 'auth:sanctum'], function () {
    Route::post('/blog', [BlogController::class, 'store']);
    Route::get('/blogs', [BlogController::class, 'index']);
    Route::get('/blog/{id}', [BlogController::class, 'show']);
    Route::post('/blog/{id}/update', [BlogController::class, 'update']);
    Route::delete('/blog/{id}', [BlogController::class, 'delete']);

    Route::post('/category', [CategoryController::class, 'store']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/category/{id}', [CategoryController::class, 'show']);
    Route::post('/category/{id}/update', [CategoryController::class, 'update']);
    Route::delete('/category/{id}', [CategoryController::class, 'delete']);

});

Route::get('/unauthorized', function () {
   return response(['message' => 'Unauthorized'], 401);
});





