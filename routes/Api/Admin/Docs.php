<?php

use App\Http\Controllers\Admin\DocumentController;
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


// Route::get('logs', [LoggerController::class, 'display']);
Route::get('/doc/', [DocumentController::class, 'displayDoc']);
Route::group(["prefix" => "doc"], function () {
    Route::get('/display', [DocumentController::class, 'displayDoc']);
    Route::post('/create', [DocumentController::class, 'createDoc']);
});
