<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pago_Mensual extends Model
{
    protected $table = 'Pago_Mensual';

    protected $fillable = [
        'mail',
        'ID_Pago_Mensual',
        'Mes',
        'Monto',
        'Archivo_Comprobante' => 'null',
        'Fecha_Subida',
        'Estado_Pago',
        'Comprobante_Inicial',
    ];

}
