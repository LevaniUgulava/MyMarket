<?php

use App\Http\Controllers\Admin\CategoryController;
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
Route::group(['prefix' => 'admin/categories', 'middleware' => ['adminpanel', 'auth:sanctum']], function () {


    Route::group(['middleware' => 'editor'], function () {
        Route::get('/maincategory', [CategoryController::class, 'displaymain']);
        Route::post('/maincategory/create', [CategoryController::class, 'Maincategory']);
        Route::post('/maincategory/delete/{id}', [CategoryController::class, 'Maincategorydelete']);

        Route::get('/category', [CategoryController::class, 'displaycategory']);
        Route::post('/category/create', [CategoryController::class, 'category']);
        Route::post('/category/delete/{id}', [CategoryController::class, 'categorydelete']);

        Route::get('/subcategory', [CategoryController::class, 'displaysub']);
        Route::post('/subcategory/create', [CategoryController::class, 'Subcategory']);
        Route::post('/subcategory/delete/{id}', [CategoryController::class, 'Subcategorydelete']);
    });
});
