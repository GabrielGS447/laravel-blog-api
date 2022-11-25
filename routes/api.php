<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::controller(UserController::class)->group(function () {
  Route::get('/users', 'index');
  Route::get('/users/{user}', 'show');
  Route::post('/users', 'store');
  Route::patch('/users/{user}', 'update');
  Route::delete('/users/{user}', 'destroy');
});

Route::controller(PostController::class)->group(function (){
  Route::get('/posts', 'index');
  Route::get('/posts/{post}', 'show');
  Route::post('/posts', 'store');
});
