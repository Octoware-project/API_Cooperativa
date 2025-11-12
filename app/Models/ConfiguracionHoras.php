<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConfiguracionHoras extends Model
{
    use HasFactory;

    protected $table = 'configuracion_horas';
    
    protected $fillable = [
        'valor_por_hora',
        'activo',
        'observaciones'
    ];

    protected $casts = [
        'valor_por_hora' => 'decimal:2',
        'activo' => 'boolean',
    ];

    /**
     * Obtener el valor por hora actualmente vigente
     */
    public static function getValorActual()
    {
        $config = self::where('activo', true)
                     ->latest('created_at')
                     ->first();
                     
        return $config ? $config->valor_por_hora : 0;
    }

    /**
     * Obtener la configuración activa actual
     */
    public static function getConfiguracionActual()
    {
        return self::where('activo', true)
                  ->latest('created_at')
                  ->first();
    }
}