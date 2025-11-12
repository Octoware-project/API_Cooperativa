<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FacturasController;

Route::get('/', function () {
    return view('welcome');
});

// Ruta pública para que el backoffice pueda acceder a los comprobantes
Route::get('/comprobantes/{id}', [FacturasController::class, 'descargarComprobante'])->name('comprobantes.descargar');
