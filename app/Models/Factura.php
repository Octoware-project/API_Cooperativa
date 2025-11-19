<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Factura extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'Factura';

    protected $fillable = [
        'email',
        'Monto',
        'Archivo_Comprobante',
        'Estado_Pago',
        'tipo_pago',
        'fecha_pago',
        'motivo'
    ];

  
    protected static function booted(): void
    {
        static::deleting(function (Factura $factura) {
            if ($factura->Archivo_Comprobante) {
                try {
                    Storage::disk('public')->delete($factura->Archivo_Comprobante);
                    \Log::info("Archivo eliminado: " . $factura->Archivo_Comprobante);
                } catch (\Exception $e) {
                    \Log::warning("No se pudo eliminar el archivo: " . $factura->Archivo_Comprobante . " - " . $e->getMessage());
                }
            }
        });

        static::forceDeleting(function (Factura $factura) {
            if ($factura->Archivo_Comprobante) {
                try {
                    Storage::disk('public')->delete($factura->Archivo_Comprobante);
                    \Log::info("Archivo eliminado (force delete): " . $factura->Archivo_Comprobante);
                } catch (\Exception $e) {
                    \Log::warning("No se pudo eliminar el archivo (force delete): " . $factura->Archivo_Comprobante . " - " . $e->getMessage());
                }
            }
        });
    }
}
