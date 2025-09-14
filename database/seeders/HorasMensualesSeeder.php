<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Horas_Mensuales;

class HorasMensualesSeeder extends Seeder
{
    public function run()
    {
        // Crear 2 registros de 5 horas cada uno para user@test.com (id 1)
        $email = 'user@test.com';
        $anio = 2025;
        $mes = 9;
        Horas_Mensuales::create([
            'email' => $email,
            'anio' => $anio,
            'mes' => $mes,
            'dia' => 1,
            'Cantidad_Horas' => 5,
            'Motivo_Falla' => null,
            'Tipo_Justificacion' => null,
            'Monto_Compensario' => 0,
            'created_at' => now()->setYear($anio)->setMonth($mes)->setDay(1)->startOfDay(),
            'updated_at' => now()->setYear($anio)->setMonth($mes)->setDay(1)->startOfDay(),
        ]);
        Horas_Mensuales::create([
            'email' => $email,
            'anio' => $anio,
            'mes' => $mes,
            'dia' => 2,
            'Cantidad_Horas' => 5,
            'Motivo_Falla' => null,
            'Tipo_Justificacion' => null,
            'Monto_Compensario' => 0,
            'created_at' => now()->setYear($anio)->setMonth($mes)->setDay(2)->startOfDay(),
            'updated_at' => now()->setYear($anio)->setMonth($mes)->setDay(2)->startOfDay(),
        ]);
    }
}
