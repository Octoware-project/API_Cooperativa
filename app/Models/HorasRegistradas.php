<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HorasRegistradas extends Model
{
    protected $table = 'horas';

    protected $fillable = [
        'ID_Personas',
        'ID_Registro_Horas',
        'Semana',
        'Cantidad_Horas',
        'Motivo_Falla',
        'Tipo_Justificacion',
        'Monto_Compensario',
    ];
}
