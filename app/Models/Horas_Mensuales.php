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
        'anio',
        'mes',
        'dia',
        'Cantidad_Horas',
        'Motivo_Falla',
        'Tipo_Justificacion',
        'Monto_Compensario',
        'valor_hora_al_momento',
        'horas_equivalentes_calculadas',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'Cantidad_Horas' => 'integer',
        'Monto_Compensario' => 'decimal:2',
        'valor_hora_al_momento' => 'decimal:2',
        'horas_equivalentes_calculadas' => 'decimal:2',
    ];

    public function getHorasEquivalentes()
    {
        if ($this->horas_equivalentes_calculadas !== null) {
            return $this->horas_equivalentes_calculadas;
        }
        
        $horasReales = $this->Cantidad_Horas ?? 0;
        
        if ($this->Monto_Compensario && $this->Monto_Compensario > 0) {
            $valorHora = $this->valor_hora_al_momento ?? ConfiguracionHoras::getValorActual();
            if ($valorHora > 0) {
                $horasDeJustificacion = $this->Monto_Compensario / $valorHora;
                return $horasReales + $horasDeJustificacion;
            }
        }
        
        return $horasReales;
    }

    public function calcularYFijarHorasEquivalentes()
    {
        $horasReales = $this->Cantidad_Horas ?? 0;
        
        if ($this->Monto_Compensario && $this->Monto_Compensario > 0) {
            $valorHora = ConfiguracionHoras::getValorActual();
            if ($valorHora > 0) {
                $this->valor_hora_al_momento = $valorHora;
                $horasDeJustificacion = $this->Monto_Compensario / $valorHora;
                $this->horas_equivalentes_calculadas = $horasReales + $horasDeJustificacion;
            } else {
                $this->horas_equivalentes_calculadas = $horasReales;
            }
        } else {
            $this->horas_equivalentes_calculadas = $horasReales;
        }
        
        return $this;
    }

    public function getHorasJustificacion()
    {
        if (!$this->Monto_Compensario || $this->Monto_Compensario <= 0) {
            return 0;
        }
        
        $valorHora = $this->valor_hora_al_momento ?? ConfiguracionHoras::getValorActual();
        return $valorHora > 0 ? $this->Monto_Compensario / $valorHora : 0;
    }
}
