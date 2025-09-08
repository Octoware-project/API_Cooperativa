<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pago_Mensual;
use App\Models\Horas_Mensuales;
use Illuminate\Support\Facades\Storage;

class Facturas_y_Pagos extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear 10 facturas de prueba
        $comprobantesEjemplo = [
            'comprobante1.pdf',
            'comprobante2.pdf',
            'comprobante3.pdf',
        ];
        $rutasComprobantes = [];
        foreach ($comprobantesEjemplo as $archivo) {
            $origen = database_path('seeders/files/comprobantes/' . $archivo);
            if (file_exists($origen)) {
                $ruta = Storage::disk('public')->putFileAs('comprobantes', $origen, $archivo);
                $rutasComprobantes[] = $ruta;
            } else {
                $rutasComprobantes[] = null;
            }
        }

        Pago_Mensual::factory()->count(10)->make()->each(function ($pago, $i) use ($rutasComprobantes) {
            if ($i < 3 && $rutasComprobantes[$i]) {
                $pago->Archivo_Comprobante = $rutasComprobantes[$i];
            } else {
                $pago->Archivo_Comprobante = null;
            }
            $pago->save();
        });

        // Crear 10 horas mensuales de prueba
        Horas_Mensuales::factory()->count(10)->create();
    }
}
