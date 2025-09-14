<?php

namespace Database\Factories;

use App\Models\Horas_Mensuales;
use Illuminate\Database\Eloquent\Factories\Factory;

class Horas_MensualesFactory extends Factory
{
    protected $model = Horas_Mensuales::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'anio' => $this->faker->year(),
            'mes' => $this->faker->numberBetween(1, 12),
            'dia' => $this->faker->numberBetween(1, 28),
            'Cantidad_Horas' => $this->faker->numberBetween(10, 60),
            'Motivo_Falla' => $this->faker->optional()->sentence(),
            'Tipo_Justificacion' => $this->faker->optional()->word(),
            'Monto_Compensario' => $this->faker->randomFloat(2, 0, 5000),
        ];
    }
}
