<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
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


Route::get('/display', [ProductController::class, 'display']);
Route::post('/addproduct', [ProductController::class, 'create'])->middleware('auth:sanctum');
Route::post('/Search', [ProductController::class, 'filterbyname']);
Route::get('/Searchcategory/{id}', [ProductController::class, 'filterbycategory']);
