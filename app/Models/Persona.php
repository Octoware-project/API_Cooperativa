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
    ];

    protected $casts = [
        'fechaNacimiento' => 'date',
    ];
}