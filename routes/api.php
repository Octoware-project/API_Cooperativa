<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ValidarUsuarioController;
use App\Http\Controllers\horasController;
use App\Http\Controllers\FacturasController;
use App\Http\Controllers\PagoMensualController;
use App\Http\Controllers\ConexionController;
use App\Http\Middleware\ValidarUsuario;


// Ruta de prueba que usa el middleware
Route::get('/test-token', function (\Illuminate\Http\Request $request) {
    return response()->json([
        'mensaje' => 'Token válido',
        'usuario' => $request->attributes->get('user')
    ]);
});


Route::middleware('auth:api')->group(function () {
    Route::post('/horas/calcular', [HorasController::class, 'CalcularHorasRegistradas']);
    Route::post('/horas/agregar', [HorasController::class, 'AgregarHorasRegistradas']);
    Route::put('/horas/editar', [HorasController::class, 'EditarHorasRegistradas']);
    Route::delete('/horas/eliminar', [HorasController::class, 'EliminarHoras']);
    Route::get('/horas/mostrar', [HorasController::class, 'MostrarHorasRegistradas']);
    Route::get('/horas/buscar', [HorasController::class, 'BuscarParaEditar']); 
    
    Route::post('/facturas/agregar', [FacturasController::class, 'AgregarPagoMensual']);
    Route::put('/facturas/editar', [FacturasController::class, 'EditarPagoMensual']);
    Route::delete('/facturas/eliminar', [FacturasController::class, 'EliminarPagoMensual']);
    Route::get('/facturas/mostrar', [FacturasController::class, 'MostrarPagoMensual']);
    Route::get('/facturas/buscar', [FacturasController::class, 'BuscarParaEditar']);   
    Route::get('/facturas/calcular', [FacturasController::class, 'CalcularElTotalDeLasFacturas']);
});

