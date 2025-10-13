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

    // Cache estático para valor por hora
    private static $valorCache = null;
    private static $cacheTime = null;
    private static $cacheDuration = 3600; // 1 hora en segundos

    /**
     * Obtener el valor por hora actualmente vigente (con cache)
     */
    public static function getValorActual()
    {
        // Verificar si el cache es válido
        if (self::$valorCache !== null && 
            self::$cacheTime !== null && 
            (time() - self::$cacheTime) < self::$cacheDuration) {
            return self::$valorCache;
        }

        // Cache expirado o no existe, consultar BD
        $config = self::where('activo', true)
                     ->latest('created_at')
                     ->first();
        
        // Actualizar cache
        self::$valorCache = $config ? $config->valor_por_hora : 1000; // Fallback a 1000
        self::$cacheTime = time();
                     
        return self::$valorCache;
    }

    /**
     * Limpiar cache (útil cuando se actualiza la configuración)
     */
    public static function clearCache()
    {
        self::$valorCache = null;
        self::$cacheTime = null;
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