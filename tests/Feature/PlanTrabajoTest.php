<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\PlanTrabajo;
use App\Models\Horas_Mensuales;

class PlanTrabajoTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lista_planes_del_usuario()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $otro = User::factory()->create(['email' => 'otro@ejemplo.com']);
        PlanTrabajo::factory()->create(['user_id' => $user->id, 'mes' => 9, 'anio' => 2025]);
        PlanTrabajo::factory()->create(['user_id' => $user->id, 'mes' => 8, 'anio' => 2025]);
        PlanTrabajo::factory()->create(['user_id' => $otro->id, 'mes' => 9, 'anio' => 2025]);
        $response = $this->getJson('/api/planes-trabajo');
        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['mes' => 9])
            ->assertJsonFragment(['mes' => 8])
            ->assertJsonMissing(['user_id' => $otro->id]);
    }

    public function test_store_crea_plan_para_usuario()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $payload = [
            'mes' => 9,
            'anio' => 2025,
            'horas_requeridas' => 40
        ];
        $response = $this->postJson('/api/planes-trabajo', $payload);
        $response->assertStatus(201)
            ->assertJsonFragment(['mes' => 9, 'anio' => 2025, 'horas_requeridas' => 40]);
        $this->assertDatabaseHas('plan_trabajos', [
            'user_id' => $user->id,
            'mes' => 9,
            'anio' => 2025,
            'horas_requeridas' => 40
        ]);
    }

    public function test_progreso_devuelve_horas_cumplidas_y_porcentaje()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $plan = PlanTrabajo::factory()->create([
            'user_id' => $user->id,
            'mes' => 9,
            'anio' => 2025,
            'horas_requeridas' => 10
        ]);
        Horas_Mensuales::factory()->create([
            'email' => 'test@example.com',
            'mes' => 9,
            'anio' => 2025,
            'Cantidad_Horas' => 4
        ]);
        Horas_Mensuales::factory()->create([
            'email' => 'test@example.com',
            'mes' => 9,
            'anio' => 2025,
            'Cantidad_Horas' => 3
        ]);
        $response = $this->getJson('/api/planes-trabajo/' . $plan->id . '/progreso');
        $response->assertStatus(200)
            ->assertJson([
                'horas_requeridas' => 10,
                'horas_cumplidas' => 7,
                'porcentaje' => 70.0
            ]);
    }
}
