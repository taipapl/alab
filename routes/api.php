<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ResultsController;

Route::get('/tokenCheck', function () {
    return response()->json(['status' => 'ok']);
})->middleware('auth:api')->name('tokenCheck');

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->get('/results', [ResultsController::class, 'index']);