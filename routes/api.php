<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ProductController;


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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/login', function (Request $request) {
    return response()->json([
        'message' => 'Unauthorized'
    ], 401);
})->name('login');


Route::middleware('auth:sanctum')->group( function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/create_token', [AuthController::class, 'create_token']);
    Route::get('/get_user', [AuthController::class, 'get_user']);

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::post('products/create', [ProductController::class, 'store']);
    Route::get('products/edit/{product}',  [ProductController::class, 'edit']);
    Route::put('products/update/{product}',  [ProductController::class, 'update']);
    Route::delete('products/delete/{product}',  [ProductController::class, 'destroy']);
});
