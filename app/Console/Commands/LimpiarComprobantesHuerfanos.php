<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Factura;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class LimpiarComprobantesHuerfanos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comprobantes:limpiar {--dry-run : Mostrar qué archivos se eliminarían sin eliminarlos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina archivos de comprobantes que no están asociados a facturas activas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('🔍 Analizando archivos de comprobantes...');
        
        // Obtener todos los archivos de comprobantes activos
        $archivosActivos = Factura::whereNotNull('Archivo_Comprobante')
            ->pluck('Archivo_Comprobante')
            ->map(function($archivo) {
                return basename($archivo);
            })
            ->toArray();
            
        $this->info('📄 Archivos de comprobantes activos en BD: ' . count($archivosActivos));
        
        // Directorios a limpiar
        $dirs = [
            storage_path('app/public/comprobantes/'),
            public_path('storage/comprobantes/')
        ];

        $totalArchivos = 0;
        $totalHuerfanos = 0;
        $espacioLiberado = 0;
        $archivosEliminados = 0;

        foreach ($dirs as $rutaComprobantes) {
            if (!File::exists($rutaComprobantes)) {
                $this->warn('❌ El directorio no existe: ' . $rutaComprobantes);
                continue;
            }

            $archivosFilesystem = collect(File::files($rutaComprobantes))
                ->map(function($file) {
                    return $file->getFilename();
                })
                ->toArray();

            $totalArchivos += count($archivosFilesystem);

            // Encontrar archivos huérfanos
            $archivosHuerfanos = array_diff($archivosFilesystem, $archivosActivos);
            $totalHuerfanos += count($archivosHuerfanos);

            if (empty($archivosHuerfanos)) {
                $this->info('✅ No se encontraron archivos huérfanos en: ' . $rutaComprobantes);
                continue;
            }

            $this->warn('🗑️  Archivos huérfanos encontrados en ' . $rutaComprobantes . ': ' . count($archivosHuerfanos));

            foreach ($archivosHuerfanos as $archivo) {
                $rutaCompleta = $rutaComprobantes . $archivo;
                $tamano = File::exists($rutaCompleta) ? File::size($rutaCompleta) : 0;
                $espacioLiberado += $tamano;

                if ($dryRun) {
                    $this->line("  📋 Sería eliminado: {$archivo} (" . $this->formatBytes($tamano) . ")");
                } else {
                    try {
                        File::delete($rutaCompleta);
                        $archivosEliminados++;
                        $this->line("  🗑️  Eliminado: {$archivo} (" . $this->formatBytes($tamano) . ")");
                    } catch (\Exception $e) {
                        $this->error("  ❌ Error eliminando {$archivo}: " . $e->getMessage());
                    }
                }
            }
        }

        $this->info('📁 Total de archivos analizados: ' . $totalArchivos);
        $this->info('🗑️  Total de huérfanos encontrados: ' . $totalHuerfanos);

        if ($dryRun) {
            $this->info("\n📊 SIMULACIÓN COMPLETADA");
            $this->info("   Archivos que serían eliminados: " . $totalHuerfanos);
            $this->info("   Espacio que se liberaría: " . $this->formatBytes($espacioLiberado));
            $this->comment("\n💡 Para ejecutar la limpieza real, ejecuta: php artisan comprobantes:limpiar");
        } else {
            $this->info("\n✅ LIMPIEZA COMPLETADA");
            $this->info("   Archivos eliminados: " . $archivosEliminados);
            $this->info("   Espacio liberado: " . $this->formatBytes($espacioLiberado));
        }
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}