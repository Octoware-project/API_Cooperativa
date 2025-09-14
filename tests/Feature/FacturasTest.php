<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Factura;

class FacturasTest extends TestCase
{
	use RefreshDatabase;

	public function test_agregar_factura_exito()
	{
		$payload = [
			'Monto' => 500,
			'Estado_Pago' => 'Pendiente',
			'tipo_pago' => 'Transferencia',
			'Mes' => 'Enero',
			'Anio' => 2024
		];

		$response = $this->postJson('/api/facturas', $payload);
		$response->assertStatus(201)
			->assertJsonFragment(["Factura agregada con exito" => true]);

		$this->assertDatabaseHas('Factura', [
			'email' => 'test@example.com',
			'Monto' => 500,
			'Estado_Pago' => 'Pendiente',
			'tipo_pago' => 'Transferencia',
		]);
	}

	public function test_agregar_factura_monto_demasiado_grande()
	{
		$payload = [
			'Monto' => 1000000,
			'Estado_Pago' => 'Pendiente',
			'tipo_pago' => 'Transferencia',
			'Mes' => 'Enero',
			'Anio' => 2024
		];
		$response = $this->postJson('/api/facturas', $payload);
		$response->assertStatus(422)
			->assertJsonFragment(['error' => 'Un monto demasiado grande']);
	}

	public function test_listar_facturas_por_usuario()
	{
		Factura::factory()->create([
			'email' => 'test@example.com',
			'Monto' => 100,
		]);
		Factura::factory()->create([
			'email' => 'test@example.com',
			'Monto' => 200,
		]);
		Factura::factory()->create([
			'email' => 'otro@ejemplo.com',
			'Monto' => 300,
		]);

		$response = $this->getJson('/api/facturas');
		$response->assertStatus(200)
			->assertJsonCount(2, 'facturas')
			->assertJsonFragment(['Monto' => 100])
			->assertJsonFragment(['Monto' => 200])
			->assertJsonMissing(['Monto' => 300]);
	}

	public function test_detalle_factura()
	{
		$factura = Factura::factory()->create([
			'email' => 'test@example.com',
			'Monto' => 150,
			'Archivo_Comprobante' => 'comprobantes/test.jpg',
		]);

		$response = $this->getJson('/api/facturas/' . $factura->id);
		$response->assertStatus(200)
			->assertJsonFragment(['Monto' => 150])
			->assertJsonStructure(['factura' => ['imagen_comprobante']]);
	}

	public function test_filtrar_facturas_por_mes_y_anio()
	{
		Factura::factory()->create([
			'email' => 'test@example.com',
			'Monto' => 100,
			'created_at' => '2024-01-15',
		]);
		Factura::factory()->create([
			'email' => 'test@example.com',
			'Monto' => 200,
			'created_at' => '2024-02-10',
		]);
		Factura::factory()->create([
			'email' => 'test@example.com',
			'Monto' => 300,
			'created_at' => '2023-01-10',
		]);

		$payload = [
			'mes' => 1,
			'anio' => 2024
		];
		$response = $this->postJson('/api/facturas/filtrar', $payload);
		$response->assertStatus(200)
			->assertJsonCount(1, 'facturas')
			->assertJsonFragment(['Monto' => 100])
			->assertJsonMissing(['Monto' => 200])
			->assertJsonMissing(['Monto' => 300]);
	}

	public function test_cancelar_factura()
	{
		$factura = Factura::factory()->create([
			'email' => 'test@example.com',
			'Monto' => 123,
		]);

		$response = $this->deleteJson('/api/facturas/' . $factura->id);
		$response->assertStatus(200)
			->assertJsonFragment(['mensaje' => 'Factura cancelada correctamente']);

		$this->assertSoftDeleted('Factura', [
			'id' => $factura->id
		]);
	}
}
