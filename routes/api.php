
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\Autenticacion;
use App\Http\Controllers\Horas_MensualesController;
use App\Http\Controllers\FacturasController;
use App\Http\Controllers\PlanTrabajoController;
use App\Http\Controllers\UserController;

Route::middleware([Autenticacion::class])->group(function () {
   
    // Rutas para Plan de Trabajo
    Route::get('/planes-trabajo', [PlanTrabajoController::class, 'index']);
    Route::get('/planes-trabajo-optimizado', [PlanTrabajoController::class, 'indexWithProgress']); // NUEVO ENDPOINT AGREGADO
    Route::get('/planes-trabajo-dashboard', [PlanTrabajoController::class, 'dashboard']); // ENDPOINT TODO-EN-UNO SÚPER OPTIMIZADO
    Route::post('/planes-trabajo', [PlanTrabajoController::class, 'store']);
    Route::get('/planes-trabajo/{id}/progreso', [PlanTrabajoController::class, 'progreso']);

    // Rutas para Horas Mensuales
    Route::get('/horas', [Horas_MensualesController::class, "index"]);
    Route::get('/horas/ultimo-mes', [Horas_MensualesController::class, 'sumarHorasUltimoMes']);
    Route::get('/dashboard-horas', [Horas_MensualesController::class, 'dashboardHoras']); // NUEVO ENDPOINT OPTIMIZADO
    Route::post('/horas', [Horas_MensualesController::class, "store"]);
    Route::post('/horas/calcular', [Horas_MensualesController::class, "calcularHorasRegistradas"]);
    Route::post('/horas/justificacion', [Horas_MensualesController::class, "agregarJustificacion"]);
    Route::get('/horas/{id}', [Horas_MensualesController::class, "show"]);
    Route::put('/horas/{id}', [Horas_MensualesController::class, "update"]);
    Route::delete('/horas/{id}', [Horas_MensualesController::class, "destroy"]);

    // Rutas para Facturas
    Route::post('/facturas', [FacturasController::class, 'agregarFactura']);
    Route::get('/facturas/{id}', [FacturasController::class, 'detalle']);
    Route::get('/facturas/{id}/url-comprobante', [FacturasController::class, 'urlComprobante']);
    Route::get('/facturas/{id}/comprobante', [FacturasController::class, 'servirComprobante']);
    Route::delete('/facturas/{id}', [FacturasController::class, 'cancelarFactura']);
    Route::get('/facturas', [FacturasController::class, 'listarFacturasPorUsuario']);
    Route::post('/facturas/filtrar', [FacturasController::class, 'filtrarFacturas']);

    // Rutas para gestión de usuarios (proxy a API Usuarios)
    Route::post('/completar-datos', [UserController::class, 'completarDatos']);
    Route::post('/editar-datos-persona', [UserController::class, 'editarDatosPersona']);
    Route::get('/datos-usuario', [UserController::class, 'obtenerDatosUsuario']);
    Route::post('/cambiar-contrasena', [UserController::class, 'cambiarContrasena']);

});