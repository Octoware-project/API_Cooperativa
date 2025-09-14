<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlanTrabajo extends Model
{
    use HasFactory;
    protected $table = 'plan_trabajos';
    protected $fillable = ['user_id', 'mes', 'anio', 'horas_requeridas'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Horas mensuales del usuario en el mes/año de este plan (por email)
    public function horasMensuales()
    {
        return $this->user->horasMensuales()
            ->where('anio', $this->anio)
            ->where('mes', $this->mes);
    }

    // Total de horas cargadas en el mes/año de este plan
    public function horasCumplidas()
    {
        return $this->horasMensuales()->sum('Cantidad_Horas');
    }
}
