<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Horas_Mensuales extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'horas_mensuales';
    protected $fillable = [
        'email',
        'Semana',
        'Cantidad_Horas',
        'Motivo_Falla',
        'Tipo_Justificacion',
        'Monto_Compensario',
    ];
}
