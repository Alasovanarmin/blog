<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Site\BlogController;
use App\Http\Controllers\Site\BlogCommentController;

Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);

Route::group(["middleware" => 'auth:sanctum'], function () {
    Route::get('/blogs', [BlogController::class, 'index']);
    Route::get('/blog/{id}', [BlogController::class, 'show']);

    //Comments
    Route::post('/blog/{id}/comment', [BlogCommentController::class, 'store']);
    Route::get('/blog/{id}/comments', [BlogCommentController::class, 'index']);
    Route::post('/blog/comment/{id}/update', [BlogCommentController::class, 'update']);
    Route::delete('/blog/comment/{id}', [BlogCommentController::class, 'delete']);

    //Give Star
    Route::post('/blog/{id}/star', [BlogController::class, 'rateStar']);

    //Like
    Route::post('/blog/{id}/like', [BlogController::class, 'like']);

});

Route::get('/unauthorized', function () {
   return response(['message' => 'Unauthorized'], 401);
});





