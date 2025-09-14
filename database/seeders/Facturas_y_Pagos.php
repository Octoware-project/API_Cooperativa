<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Factura;
use App\Models\Horas_Mensuales;
use Illuminate\Support\Facades\Storage;

class Facturas_y_Pagos extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear 3 facturas para el usuario con id 1 y email user@test.com, cada una con un Archivo_Comprobante de prueba
        $userId = 1;
        $email = 'user@test.com';
        $comprobantes = [
            'seeders/files/comprobantes/comprobante1.pdf',
            'seeders/files/comprobantes/comprobante2.pdf',
            'seeders/files/comprobantes/comprobante3.pdf',
        ];
        for ($i = 0; $i < 3; $i++) {
            Factura::create([
                'email' => $email,
                'Monto' => 1000 + $i * 100, // Monto ejemplo
                'Archivo_Comprobante' => $comprobantes[$i],
                'Estado_Pago' => 'Pendiente',
                'tipo_pago' => 'Transferencia',
                'fecha_pago' => null,
            ]);
        }
    }
}
