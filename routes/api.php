<?php

use App\Http\Controllers\AutenticacionController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RoleController;
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
   
});

Route::post('registro',[AutenticacionController::class, 'registro']);
Route::post('ingreso',[AutenticacionController::class, 'ingreso'] );



Route::group(['middleware' => ['auth:sanctum']], function(){
    Route::post('cerrarSesion',[AutenticacionController::class, 'cerrarSesion']);
    Route::apiResource('marcas', MarcaController::class);
    Route::apiResource('categorias', CategoriaController::class);
    Route::apiResource('productos', ProductoController::class);
    Route::apiResource('compras', CompraController::class);
    Route::get('categorias/{id}/productos', [CategoriaController::class, 'productosPorCategoria']);
    Route::get('marcas/{id}/productos',[MarcaController::class, 'productosPorMarca']);
    Route::apiResource('roles',RoleController::class)->middleware('can:roles');
});