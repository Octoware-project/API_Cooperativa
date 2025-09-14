<?php

namespace Database\Factories;

use App\Models\PlanTrabajo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanTrabajoFactory extends Factory
{
    protected $model = PlanTrabajo::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'mes' => $this->faker->numberBetween(1, 12),
            'anio' => now()->year,
            'horas_requeridas' => $this->faker->numberBetween(80, 200),
        ];
    }
}
