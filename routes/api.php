<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MntProductoController;
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


Route::controller(AuthController::class)->group(function (){
    Route::post('/login', 'login');
});

Route::prefix('/mantenimiento-producto')->group(function () {
    Route::get('/search-product-individual/{id}', [MntProductoController::class, 'detalle_producto'])->middleware('auth', 'api');
    Route::get('/index-product', [MntProductoController::class, 'index_producto'])->middleware('auth', 'api');
    Route::post('/update-product/{id}', [MntProductoController::class, 'actualizar_producto'])->middleware('auth', 'api');
    Route::post('/create-product', [MntProductoController::class, 'agregar_producto'])->middleware('auth', 'api');
    Route::put('/change-product/{id}', [MntProductoController::class, 'cambio_estado_producto'])->middleware('auth', 'api');

    Route::post('/agregar-archivo', [MntProductoController::class, 'agregar_archivo'])->middleware('auth', 'api');
    Route::delete('/eliminar-archivo/{id}', [MntProductoController::class, 'eliminar_archivos'])->middleware('auth', 'api');
    Route::get('/listado-archivos', [MntProductoController::class, 'listado_archivos'])->middleware('auth', 'api');
    Route::get('/detalle-archivo/{id}', [MntProductoController::class, 'detalle_archivo'])->middleware('auth', 'api');

});