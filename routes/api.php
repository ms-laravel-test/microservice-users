<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('users' , [UserController::class , 'index']);
Route::get('/user/{userId}/posts', [PostController::class, 'show']);
