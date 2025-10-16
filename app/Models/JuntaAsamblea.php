<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class JuntaAsamblea extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'juntas_asambleas';

    protected $fillable = [
        'lugar',
        'fecha', 
        'detalle'
    ];

    protected $casts = [
        'fecha' => 'date'
    ];

    /**
     * Scope para obtener asambleas ordenadas por fecha descendente (más reciente primero)
     */
    public function scopeOrderByFechaDesc($query)
    {
        return $query->orderBy('fecha', 'desc');
    }

    /**
     * Scope para obtener asambleas futuras
     */
    public function scopeFuturas($query)
    {
        return $query->where('fecha', '>=', now()->format('Y-m-d'));
    }

    /**
     * Scope para obtener asambleas pasadas
     */
    public function scopePasadas($query)
    {
        return $query->where('fecha', '<', now()->format('Y-m-d'));
    }
}