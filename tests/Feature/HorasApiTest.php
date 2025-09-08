<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class HorasApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Crea un usuario de prueba y simula autenticación
        $this->user = User::factory()->create();
    }

    public function test_index_horas()
    {
        $response = $this->actingAs($this->user)->getJson('/api/horas');
        $response->assertStatus(200);
    }

    public function test_detalle_horas()
    {
        $hora = \App\Models\Horas_Mensuales::factory()->create();
        $response = $this->actingAs($this->user)->getJson('/api/horas/' . $hora->id);
        $response->assertStatus(200);
    }

    public function test_agregar_horas_registradas()
    {
        $data = [
            'email' => 'test@example.com',
            'Semana' => 1,
            'Cantidad_Horas' => 10,
            'Motivo_Falla' => 'Ninguna',
            'Tipo_Justificacion' => 'N/A',
            'Monto_Compensario' => 0,
        ];
        $response = $this->actingAs($this->user)->postJson('/api/horas', $data);
        $response->assertStatus(201);
    }

    public function test_calcular_horas_registradas()
    {
        $response = $this->actingAs($this->user)->postJson('/api/horas/calcular', [
            'email' => 'test@example.com',
            'mes' => 'Enero',
        ]);
        $response->assertStatus(200);
    }

    public function test_eliminar_horas()
    {
        $hora = \App\Models\Horas_Mensuales::factory()->create();
        $response = $this->actingAs($this->user)->deleteJson('/api/horas/' . $hora->id);
        $response->assertStatus(200);
    }

    public function test_editar_horas_registradas()
    {
        $hora = \App\Models\Horas_Mensuales::factory()->create();
        $data = [
            'Cantidad_Horas' => 20,
        ];
        $response = $this->actingAs($this->user)->patchJson('/api/horas/' . $hora->id, $data);
        $response->assertStatus(200);
        $this->assertEquals(20, $response->json('Cantidad_Horas'));
    }
}
