<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Horas_Mensuales extends Model
{
    use SoftDeletes;
    protected $table = 'Horas_Mensuales';
    protected $fillable = [
        'mail',
        'ID_Registro_Horas',
        'Semana',
        'Cantidad_Horas',
        'Motivo_Falla',
        'Tipo_Justificacion',
        'Monto_Compensario',
    ];
}
