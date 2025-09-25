<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Horas_Mensuales;
use Carbon\Carbon;

class HorasMensualesTest extends TestCase
{
    use RefreshDatabase;

    public function test_agregarHorasExito()
    {
        $payload = [
            'anio' => 2025,
            'mes' => 9,
            'dia' => 14,
            'Cantidad_Horas' => 5,
            'Monto_Compensario' => 100,
            'Motivo_Falla' => 'Falla de sistema',
            'Tipo_Justificacion' => 'Enfermedad',
        ];
        $response = $this->postJson('/api/horas', $payload);
        $response->assertStatus(201)
            ->assertJsonFragment(['Cantidad_Horas' => 5]);
    $this->assertDatabaseHas('horas_mensuales', [
            'email' => 'test@example.com',
            'Cantidad_Horas' => 5,
        ]);
    }

    public function test_listarHorasPorUsuario()
    {
        Horas_Mensuales::factory()->create([
            'email' => 'test@example.com',
            'Cantidad_Horas' => 3,
        ]);
        Horas_Mensuales::factory()->create([
            'email' => 'otro@ejemplo.com',
            'Cantidad_Horas' => 7,
        ]);
        $response = $this->getJson('/api/horas/usuario');
        $response->assertStatus(200)
            ->assertJsonFragment(['Cantidad_Horas' => 3])
            ->assertJsonMissing(['Cantidad_Horas' => 7]);
    }

    public function test_sumarHorasUltimoMes()
    {
        $now = Carbon::now();
        Horas_Mensuales::factory()->create([
            'email' => 'test@example.com',
            'Cantidad_Horas' => 2,
            'created_at' => $now,
        ]);
        Horas_Mensuales::factory()->create([
            'email' => 'test@example.com',
            'Cantidad_Horas' => 4,
            'created_at' => $now,
        ]);
        Horas_Mensuales::factory()->create([
            'email' => 'test@example.com',
            'Cantidad_Horas' => 10,
            'created_at' => $now->copy()->subMonth(),
        ]);
        $response = $this->getJson('/api/horas/ultimo-mes');
        $response->assertStatus(200)
            ->assertJson(['total_horas_ultimo_mes' => 6]);
    }

    public function test_calcularHorasRegistradasPorMesYAnio()
    {
        Horas_Mensuales::factory()->create([
            'email' => 'test@example.com',
            'Cantidad_Horas' => 2,
            'mes' => 9,
            'anio' => 2025,
        ]);
        Horas_Mensuales::factory()->create([
            'email' => 'test@example.com',
            'Cantidad_Horas' => 3,
            'mes' => 9,
            'anio' => 2025,
        ]);
        Horas_Mensuales::factory()->create([
            'email' => 'test@example.com',
            'Cantidad_Horas' => 5,
            'mes' => 8,
            'anio' => 2025,
        ]);
        $payload = [
            'mes' => 9,
            'anio' => 2025
        ];
        $response = $this->postJson('/api/horas/calcular', $payload);
        $response->assertStatus(200)
            ->assertJson(['total_horas' => 5]);
    }

    public function test_detalleHoras()
    {
        $horas = Horas_Mensuales::factory()->create([
            'email' => 'test@example.com',
            'Cantidad_Horas' => 8,
        ]);
        $response = $this->getJson('/api/horas/' . $horas->id);
        $response->assertStatus(200)
            ->assertJsonFragment(['Cantidad_Horas' => 8]);
    }

    public function test_eliminarHorasMenor_24h()
    {
        $horas = Horas_Mensuales::factory()->create([
            'email' => 'test@example.com',
            'created_at' => now()->subHours(2),
        ]);
        $response = $this->deleteJson('/api/horas/' . $horas->id);
        $response->assertStatus(200)
            ->assertJsonFragment(['mensaje' => 'Horas ' . $horas->id . ' eliminadas']);
    $this->assertSoftDeleted('horas_mensuales', [
            'id' => $horas->id
        ]);
    }

    public function test_eliminarHorasMayor24h()
    {
        $horas = Horas_Mensuales::factory()->create([
            'email' => 'test@example.com',
            'created_at' => now()->subHours(30),
        ]);
        $response = $this->deleteJson('/api/horas/' . $horas->id);
        $response->assertStatus(403)
            ->assertJsonFragment(['error' => 'Solo se pueden cancelar registros con menos de 24 horas de creados']);
    }
}
