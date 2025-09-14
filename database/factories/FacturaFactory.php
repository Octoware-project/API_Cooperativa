<?php

namespace Database\Factories;

use App\Models\Factura;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacturaFactory extends Factory
{
    protected $model = Factura::class;

    public function definition()
    {
        return [
            'email' => $this->faker->safeEmail(),
            'Monto' => $this->faker->randomFloat(2, 100, 5000),
            'Archivo_Comprobante' => null,
            'Estado_Pago' => $this->faker->randomElement(['Pendiente', 'Pagado']),
            'tipo_pago' => $this->faker->randomElement(['Transferencia', 'Efectivo', 'Cheque']),
            'created_at' => $this->faker->dateTimeBetween('-3 years', 'now'),
            'updated_at' => now(),
        ];
    }
}
