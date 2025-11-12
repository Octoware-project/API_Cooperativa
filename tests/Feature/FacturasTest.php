<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Factura;

class FacturasTest extends TestCase
{
	public function test_agregarFacturaExito()
	{
		$payload = [
			'Monto' => 1000.00,
			'Estado_Pago' => 'Pendiente',
			'tipo_pago' => 'Transferencia',
			'Mes' => 'Enero',
			'Anio' => 2024,
			'motivo' => 'Test de agregación de factura para prueba unitaria'
		];

		$response = $this->postJson('/api/facturas', $payload);
		$response->assertStatus(201)
			->assertJsonFragment(['success' => true])
			->assertJsonFragment(['mensaje' => 'Factura agregada con éxito']);

		$factura = Factura::where('email', 'user@test.com')
			->where('Monto', 1000.00)
			->where('Estado_Pago', 'Pendiente')
			->where('tipo_pago', 'Transferencia')
			->latest()
			->first();
		
		$this->assertNotNull($factura);
		
		$factura->forceDelete();
	}

	public function test_AgregarFacturaMontoDemasiadoGrande()
	{
		$payload = [
			'Monto' => 1000000.00,
			'Estado_Pago' => 'Pendiente',
			'tipo_pago' => 'Transferencia',
			'Mes' => 'Enero',
			'Anio' => 2024
		];
		$response = $this->postJson('/api/facturas', $payload);
		$response->assertStatus(422)
			->assertJsonFragment(['error' => 'Datos de validación incorrectos']);
	}

	public function test_ListarFacturasPorUsuario()
	{
		$response = $this->getJson('/api/facturas');
		$response->assertStatus(200)
			->assertJsonStructure(['facturas']);
		
		$facturas = $response->json('facturas');
		$this->assertGreaterThanOrEqual(1, count($facturas));
		
		foreach ($facturas as $factura) {
			$this->assertEquals('user@test.com', $factura['email']);
		}
	}

	public function testDetalleFactura()
	{
		$factura = Factura::where('email', 'user@test.com')->first();
		$this->assertNotNull($factura);

		$response = $this->getJson('/api/facturas/' . $factura->id);
		$response->assertStatus(200)
			->assertJsonStructure(['factura' => ['Monto', 'Estado_Pago', 'tipo_pago', 'imagen_comprobante']]);
	}

	public function test_filtrarFacturasPorMesYAnio()
	{
		$payload = [
			'mes' => 9,
			'anio' => 2025
		];
		$response = $this->postJson('/api/facturas/filtrar', $payload);
		
		$response->assertStatus(200)
			->assertJsonStructure(['facturas']);
		
		$facturas = $response->json('facturas');
		
		foreach ($facturas as $factura) {
			$this->assertEquals('user@test.com', $factura['email']);
			$fecha = \Carbon\Carbon::parse($factura['created_at']);
			$this->assertEquals(9, $fecha->month);
			$this->assertEquals(2025, $fecha->year);
		}
	}

	public function test_CancelarFactura()
	{
		$factura = Factura::create([
			'email' => 'user@test.com',
			'Monto' => 999.00,
			'Estado_Pago' => 'Pendiente',
			'tipo_pago' => 'Transferencia',
			'Archivo_Comprobante' => null,
			'motivo' => 'Test de cancelación',
		]);

		$response = $this->deleteJson('/api/facturas/' . $factura->id);
		$response->assertStatus(200)
			->assertJsonFragment(['mensaje' => 'Factura cancelada correctamente']);

		$this->assertSoftDeleted('Factura', [
			'id' => $factura->id
		]);
		
		$factura->forceDelete();
	}
}
