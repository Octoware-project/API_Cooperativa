<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\Autenticacion;
use App\Http\Controllers\Horas_MensualesController;
use App\Http\Controllers\FacturasController;

Route::middleware([Autenticacion::class])->group(function () {
    Route::get('/horas', [Horas_MensualesController::class, "Index"]);
    Route::get('/horas/{id}', [Horas_MensualesController::class, "Detalle"]);
    Route::post('/horas', [Horas_MensualesController::class, "AgregarHorasRegistradas"]);
    Route::post('/horas/calcular', [Horas_MensualesController::class, "CalcularHorasRegistradas"]);
    Route::delete('/horas/{id}', [Horas_MensualesController::class, "EliminarHoras"]);
    Route::patch('/horas/{id}', [Horas_MensualesController::class, "EditarHorasRegistradas"]);

    // Rutas para Pago_Mensual
    Route::post('/pagos', [FacturasController::class, 'AgregarPagoMensual']);
    Route::get('/pagos/{id}', [FacturasController::class, 'Detalle']);
    Route::patch('/pagos/{id}', [FacturasController::class, 'EditarPagoMensual']);
    Route::delete('/pagos/{id}', [FacturasController::class, 'EliminarPagoMensual']);
    Route::get('/pagos/{id}/total', [FacturasController::class, 'CalcularElTotalDeLasFacturas']);
});