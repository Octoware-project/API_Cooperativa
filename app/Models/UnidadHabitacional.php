<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnidadHabitacional extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'unidades_habitacionales';

    protected $fillable = [
        'numero_departamento',
        'piso',
        'estado',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function personas()
    {
        return $this->hasMany(Persona::class, 'unidad_habitacional_id');
    }

    public function planesTrabajos()
    {
        return $this->hasMany(PlanTrabajo::class, 'unidad_habitacional_id');
    }

    public function scopeEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopeOcupadas($query)
    {
        return $query->whereHas('personas');
    }

    public function scopeDisponibles($query)
    {
        return $query->whereDoesntHave('personas');
    }

    public function getNombreCompletoAttribute()
    {
        return "Dpto {$this->numero_departamento} - Piso {$this->piso}";
    }

    public function getEstaOcupadaAttribute()
    {
        return $this->personas()->count() > 0;
    }
}