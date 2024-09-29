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
Route::get('/admindisplay', [ProductController::class, 'admindisplay']);
Route::get('/display/{id}', [ProductController::class, 'displaybyid']);
Route::post('/Search', [ProductController::class, 'filterbyname']);
Route::get('/Searchcategory/{id}', [ProductController::class, 'filterbycategory']);
Route::post('/addproduct', [ProductController::class, 'create']);
Route::post('/notactive/{id}', [ProductController::class, 'notactive']);
Route::post('/active/{id}', [ProductController::class, 'active']);
Route::post('/discount', [ProductController::class, 'discount']);
Route::get('/discountproducts', [ProductController::class, 'discountproducts']);

Route::get('/getSizes', [ProductController::class, 'getSizes']);
