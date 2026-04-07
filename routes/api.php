<?php

use App\Http\Controllers\ActivityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::prefix('/activities')
    ->middleware('auth:sanctum')
    ->group( function(){

        Route::post('/', [ActivityController::class, 'store']);
        Route::get('/', [ActivityController::class, 'index']);
        Route::put('/{id}', [ActivityController::class, 'update']);
        Route::get('/me', [ActivityController::class, 'userActivities']);
        Route::get('/{id}', [ActivityController::class, 'show']);
        Route::post('/{id}/join', [ActivityController::class, 'join']);
        Route::delete('/{id}/leave', [ActivityController::class, 'leave']);
        Route::delete('/remove/{id}', [ActivityController::class, 'removeMembre']);

        // comment
        Route::post('/{id}/comment', [ActivityController::class, 'comment']);
        });
        
Route::prefix('/user')
    ->middleware('auth:sanctum')
    ->group( function(){
        Route::put('/update', [UserController::class, 'update']);
    });