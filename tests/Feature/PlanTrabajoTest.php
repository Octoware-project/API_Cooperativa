<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PlanTrabajo;
use App\Models\Horas_Mensuales;

class PlanTrabajoTest extends TestCase
{
    public function test_indexListaPlanesDelUsuario()
    {
        $response = $this->getJson('/api/planes-trabajo');
        $response->assertStatus(200)
            ->assertJsonStructure([['mes', 'anio', 'horas_requeridas']]);
        
        $planes = $response->json();
        $this->assertGreaterThanOrEqual(2, count($planes));
        
        $user = User::where('email', 'user@test.com')->first();
        $this->assertNotNull($user);
        
        foreach ($planes as $plan) {
            $this->assertEquals($user->id, $plan['user_id']);
        }
    }

    public function test_storeCreaPlanParaUsuario()
    {
        $user = User::where('email', 'user@test.com')->first();
        $this->assertNotNull($user);
        
        $mesUnico = 12;
        $anioUnico = 2026;
        
        $payload = [
            'mes' => $mesUnico,
            'anio' => $anioUnico,
            'horas_requeridas' => 40
        ];
        $response = $this->postJson('/api/planes-trabajo', $payload);
        $response->assertStatus(201)
            ->assertJsonFragment(['mes' => $mesUnico, 'anio' => $anioUnico, 'horas_requeridas' => 40]);
        
        $plan = PlanTrabajo::where('user_id', $user->id)
            ->where('mes', $mesUnico)
            ->where('anio', $anioUnico)
            ->where('horas_requeridas', 40)
            ->first();
        
        $this->assertNotNull($plan);
        $plan->delete();
    }

    public function test_progresoDevuelveHorasCumplidasYPorcentaje()
    {
        $user = User::where('email', 'user@test.com')->first();
        $this->assertNotNull($user);
        
        $plan = PlanTrabajo::where('user_id', $user->id)
            ->where('mes', 9)
            ->where('anio', 2025)
            ->first();
        $this->assertNotNull($plan);
        
        $response = $this->getJson('/api/planes-trabajo/' . $plan->id . '/progreso');
        $response->assertStatus(200)
            ->assertJsonStructure(['horas_requeridas', 'horas_cumplidas', 'porcentaje']);
        
        $data = $response->json();
        $this->assertEquals(160, $data['horas_requeridas']);
        $this->assertIsNumeric($data['horas_cumplidas']);
        $this->assertIsNumeric($data['porcentaje']);
        $this->assertGreaterThanOrEqual(0, $data['porcentaje']);
        $this->assertLessThanOrEqual(100, $data['porcentaje']);
    }
}
