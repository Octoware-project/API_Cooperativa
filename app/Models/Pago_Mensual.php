<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pago_Mensual extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'Pago_Mensual';

    protected $fillable = [
        'email',
        'ID_Pago_Mensual',
        'Mes',
        'Monto',
        'Archivo_Comprobante',
        'Fecha_Subida',
        'Estado_Pago',
        'Comprobante_Inicial',
        'tipo_pago'
    ];

}
