<?php

namespace Database\Factories;

use App\Models\Pago_Mensual;
use Illuminate\Database\Eloquent\Factories\Factory;

class Pago_MensualFactory extends Factory
{
    protected $model = Pago_Mensual::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'ID_Pago_Mensual' => $this->faker->unique()->numberBetween(1000, 9999),
            'Mes' => $this->faker->monthName(),
            'Monto' => $this->faker->randomFloat(2, 1000, 10000),
            'Archivo_Comprobante' => null,
            'Fecha_Subida' => $this->faker->date(),
            'Estado_Pago' => $this->faker->randomElement(['Pendiente', 'Pagado', 'Rechazado']),
            'Comprobante_Inicial' => $this->faker->uuid(),
            'tipo_pago' => $this->faker->randomElement(['Transferencia', 'Efectivo', 'Cheque'])
        ];
    }
}
