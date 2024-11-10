<?php

use App\Http\Controllers\Admin\CollectionController;

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


Route::group(['prefix' => 'admin/collection', 'middleware' => ['adminpanel', 'auth:sanctum']], function () {


    Route::post('/create', [CollectionController::class, 'create']);
    Route::post('/delete/{collection}', [CollectionController::class, 'deletecollection']);
    Route::get('/{collection}', [CollectionController::class, 'singlecollection']);
    Route::post('/addtocollection/{collection}/product/{product}', [CollectionController::class, 'addtocollection']);
});
Route::get('/collection/display', [CollectionController::class, 'getcollection']);
Route::get('/product/{collection}', [CollectionController::class, 'singlecollection']);
