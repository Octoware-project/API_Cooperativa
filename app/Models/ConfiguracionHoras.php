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
     * Cache estático para el valor actual
     */
    private static $valorActualCache = null;
    private static $cacheTime = null;
    
    /**
     * Obtener el valor por hora actualmente vigente (con cache)
     */
    public static function getValorActual($forceRefresh = false)
    {
        // Cache válido por 5 minutos
        $cacheValidTime = 300; // 5 minutos en segundos
        
        if ($forceRefresh || 
            self::$valorActualCache === null || 
            self::$cacheTime === null ||
            (time() - self::$cacheTime) > $cacheValidTime) {
            
            $config = self::where('activo', true)
                         ->latest('created_at')
                         ->first();
                         
            self::$valorActualCache = $config ? $config->valor_por_hora : 0;
            self::$cacheTime = time();
        }
        
        return self::$valorActualCache;
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