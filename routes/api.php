<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReserveController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/auth/request', [AuthController::class, 'request']);
Route::post('/auth/verify', [AuthController::class, 'verify']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::get('/info', [ReserveController::class, 'list'])->middleware(['auth:sanctum']);
Route::post('/reserve',[ReserveController::class, 'reserve'])->middleware(['auth:sanctum']);
