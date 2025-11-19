<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Horas_Mensuales;
use Carbon\Carbon;

class HorasMensualesTest extends TestCase
{
    public function test_agregarHorasExito()
    {
        $payload = [
            'anio' => 2025,
            'mes' => 11,
            'dia' => 3,
            'Cantidad_Horas' => 5,
            'Monto_Compensario' => 100,
            'Motivo_Falla' => 'Falla de sistema',
            'Tipo_Justificacion' => 'Enfermedad',
        ];
        $response = $this->postJson('/api/horas', $payload);
        $response->assertStatus(201)
            ->assertJsonFragment(['Cantidad_Horas' => 5]);
        
        $hora = Horas_Mensuales::where('email', 'user@test.com')
            ->where('Cantidad_Horas', 5)
            ->where('mes', 11)
            ->where('anio', 2025)
            ->latest()
            ->first();
        
        $this->assertNotNull($hora);
        $hora->forceDelete();
    }

    public function test_listarHorasPorUsuario()
    {
        $response = $this->getJson('/api/horas');
        $response->assertStatus(200)
            ->assertJsonStructure(['horas']);
        
        $horas = $response->json('horas');
        $this->assertGreaterThanOrEqual(1, count($horas));
        
        foreach ($horas as $hora) {
            $this->assertEquals('user@test.com', $hora['email']);
        }
    }

    public function test_sumarHorasUltimoMes()
    {
        $response = $this->getJson('/api/horas/ultimo-mes');
        $response->assertStatus(200)
            ->assertJsonStructure(['total_horas_ultimo_mes']);
        
        $total = $response->json('total_horas_ultimo_mes');
        $this->assertIsNumeric($total);
        $this->assertGreaterThanOrEqual(0, $total);
    }

    public function test_calcularHorasRegistradasPorMesYAnio()
    {
        $payload = [
            'mes' => 9,
            'anio' => 2025
        ];
        $response = $this->postJson('/api/horas/calcular', $payload);
        $response->assertStatus(200)
            ->assertJsonStructure(['total_horas']);
        
        $totalHoras = $response->json('total_horas');
        $this->assertIsNumeric($totalHoras);
        $this->assertGreaterThanOrEqual(0, $totalHoras);
    }

    public function test_detalleHoras()
    {
        $horas = Horas_Mensuales::where('email', 'user@test.com')->first();
        $this->assertNotNull($horas);
        
        $response = $this->getJson('/api/horas/' . $horas->id);
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['Cantidad_Horas', 'email', 'mes', 'anio']]);
    }

    public function test_eliminarHorasMenor_24h()
    {
        $horas = Horas_Mensuales::create([
            'email' => 'user@test.com',
            'anio' => 2025,
            'mes' => 11,
            'dia' => 3,
            'Cantidad_Horas' => 3,
            'Motivo_Falla' => null,
            'Tipo_Justificacion' => null,
            'Monto_Compensario' => 0,
            'created_at' => now()->subHours(2),
        ]);
        $response = $this->deleteJson('/api/horas/' . $horas->id);
        $response->assertStatus(200)
            ->assertJsonFragment(['mensaje' => 'Registro de horas ' . $horas->id . ' eliminado exitosamente']);
        $this->assertSoftDeleted('horas_mensuales', [
            'id' => $horas->id
        ]);
        
        $horas->forceDelete();
    }

    public function test_eliminarHorasMayor24h()
    {
        $horas = Horas_Mensuales::create([
            'email' => 'user@test.com',
            'anio' => 2025,
            'mes' => 11,
            'dia' => 3,
            'Cantidad_Horas' => 3,
            'Motivo_Falla' => null,
            'Tipo_Justificacion' => null,
            'Monto_Compensario' => 0,
            'created_at' => now()->subHours(30),
        ]);
        $response = $this->deleteJson('/api/horas/' . $horas->id);
        $response->assertStatus(403)
            ->assertJsonFragment(['error' => 'Solo se pueden eliminar registros con menos de 24 horas de creados']);
        
        $horas->forceDelete();
    }
}
