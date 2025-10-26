<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;



    /**
     * Tabla en la base de datos
     */
    protected $table = 'personas';

    /**
     * Relación con el modelo User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    protected $fillable = [
        'user_id',
        'unidad_habitacional_id',
        'fecha_asignacion_unidad',
        'name',
        'apellido',
        'CI',
        'telefono',
        'direccion',
        'estadoCivil',
        'genero',
        'fechaNacimiento',
        'ocupacion',
        'nacionalidad',
        'estadoRegistro',
        'fecha_aceptacion',
    ];

    protected $casts = [
        'fechaNacimiento' => 'date',
        'fecha_asignacion_unidad' => 'datetime',
        'fecha_aceptacion' => 'datetime',
    ];

 
    public function unidadHabitacional()
    {
        return $this->belongsTo(UnidadHabitacional::class, 'unidad_habitacional_id');
    }
}