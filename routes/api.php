<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PantryController;
use App\Http\Controllers\Api\V1\RecipeFinderController;

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

// Rute Publik untuk Autentikasi
Route::post('/v1/register', [AuthController::class, 'register']);
Route::post('/v1/login', [AuthController::class, 'login']);

// Rute yang Dilindungi (Harus Login untuk Mengakses)
Route::middleware('auth:sanctum')->group(function () {
    // Rute untuk user
    Route::get('/v1/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/v1/logout', [AuthController::class, 'logout']);
    
    // Rute untuk mengelola Pantry Items
    // Ini secara otomatis membuat endpoint GET, POST, PUT/PATCH, DELETE
    Route::apiResource('/v1/pantry-items', PantryController::class);
    
    // Rute untuk fitur pencarian resep
    Route::get('/v1/find-recipes', [RecipeFinderController::class, 'findRecipesByPantry']);
    
    // Rute untuk melihat detail resep berdasarkan ID
    Route::get('/v1/recipes/{id}', [RecipeFinderController::class, 'show']);
});
