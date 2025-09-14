
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\Autenticacion;
use App\Http\Controllers\Horas_MensualesController;
use App\Http\Controllers\FacturasController;
use App\Http\Controllers\PlanTrabajoController;

Route::middleware([Autenticacion::class])->group(function () {
   
    // Rutas para Plan de Trabajo
    Route::get('/planes-trabajo', [PlanTrabajoController::class, 'index']);
    Route::post('/planes-trabajo', [PlanTrabajoController::class, 'store']);
    Route::get('/planes-trabajo/{id}/progreso', [PlanTrabajoController::class, 'progreso']);

    Route::get('/horas/usuario', [Horas_MensualesController::class, 'listarHorasPorUsuario']);
    Route::get('/horas', [Horas_MensualesController::class, "Index"]);
    Route::get('/horas/ultimo-mes', [Horas_MensualesController::class, 'sumarHorasUltimoMes']);
    Route::get('/horas/{id}', [Horas_MensualesController::class, "Detalle"]);

    // Reemplaza AgregarHorasRegistradas por agregarHoras y AgregarJustificacion por agregarJustificacion
    Route::post('/horas', [Horas_MensualesController::class, "agregarHoras"]);
    Route::post('/horas/calcular', [Horas_MensualesController::class, "CalcularHorasRegistradas"]);
    Route::post('/horas/justificacion', [Horas_MensualesController::class, "AgregarJustificacion"]);
    Route::delete('/horas/{id}', [Horas_MensualesController::class, "EliminarHoras"]);

    // Rutas para Facturas
    Route::post('/facturas', [FacturasController::class, 'AgregarFactura']);
    Route::get('/facturas/{id}', [FacturasController::class, 'Detalle']);
    Route::delete('/facturas/{id}', [FacturasController::class, 'CancelarFactura']);
    Route::get('/facturas', [FacturasController::class, 'ListarFacturasPorUsuario']);
    Route::post('/facturas/filtrar', [FacturasController::class, 'filtrarFacturas']);


});