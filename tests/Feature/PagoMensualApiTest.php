<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pago_Mensual;

class PagoMensualApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_agregar_pago_mensual()
    {
        $data = [
            'email' => 'test@example.com',
            'ID_Pago_Mensual' => 1234,
            'Mes' => 'Enero',
            'Monto' => 1500.50,
            'Archivo_Comprobante' => null,
            'Fecha_Subida' => '2025-09-08',
            'Estado_Pago' => 'Pendiente',
            'Comprobante_Inicial' => null,
            'tipo_pago' => 'Transferencia',
        ];
        $response = $this->actingAs($this->user)->postJson('/api/pagos', $data);
        $response->assertStatus(201);
        $this->assertDatabaseHas('Pago_Mensual', [
            'email' => 'test@example.com',
            'ID_Pago_Mensual' => 1234,
        ]);
    }

    public function test_detalle_pago_mensual()
    {
        $pago = Pago_Mensual::factory()->create();
        $response = $this->actingAs($this->user)->getJson('/api/pagos/' . $pago->id);
        $response->assertStatus(201);
        $response->assertJsonFragment([
            'ID_Pago_Mensual' => $pago->ID_Pago_Mensual,
        ]);
    }

    public function test_editar_pago_mensual()
    {
        $pago = Pago_Mensual::factory()->create();
        $data = [
            'Mes' => 'Febrero',
            'Monto' => 2000.00,
        ];
        $response = $this->actingAs($this->user)->patchJson('/api/pagos/' . $pago->id, $data);
        $response->assertStatus(201);
        $this->assertDatabaseHas('Pago_Mensual', [
            'id' => $pago->id,
            'Mes' => 'Febrero',
            'Monto' => 2000.00,
        ]);
    }

    public function test_eliminar_pago_mensual()
    {
        $pago = Pago_Mensual::factory()->create();
        $response = $this->actingAs($this->user)->deleteJson('/api/pagos/' . $pago->id);
        $response->assertStatus(200);
        $this->assertSoftDeleted('Pago_Mensual', [
            'id' => $pago->id,
        ]);
    }

    public function test_calcular_total_pago_mensual()
    {
        $pago = Pago_Mensual::factory()->create(['Monto' => 500]);
        $response = $this->actingAs($this->user)->getJson('/api/pagos/' . $pago->id . '/total');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'TotalPagado' => 500
        ]);
    }
}
