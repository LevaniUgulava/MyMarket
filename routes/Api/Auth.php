<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\GoogleController;
use App\Models\User;
use Google\Service\Compute\Router;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
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


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/admin/login', [AuthController::class, 'adminlogin'])->name('admin.login');


Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::post('auth/facebook', [FacebookController::class, 'authenticate']);


Route::post('google/callback', [GoogleController::class, 'callback']);


Route::get('/email/verify/{id}', [AuthController::class, 'verify'])->middleware(['signed'])->name("verification.verify");


Route::get('/userstatuses', [AuthController::class, 'getuserstatus'])->middleware('auth:sanctum');
