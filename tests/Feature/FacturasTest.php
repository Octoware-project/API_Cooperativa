<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Factura;

class FacturasTest extends TestCase
{
	use RefreshDatabase;

	public function test_agregarFacturaExito()
	{
		$payload = [
			'Monto' => 1000.00,
			'Estado_Pago' => 'Pendiente',
			'tipo_pago' => 'Transferencia',
			'Mes' => 'Enero',
			'Anio' => 2024
		];

		$response = $this->postJson('/api/facturas', $payload);
		$response->assertStatus(201)
			->assertJsonFragment(['success' => true])
			->assertJsonFragment(['mensaje' => 'Factura agregada con éxito']);

		$this->assertDatabaseHas('Factura', [
			'email' => 'test@example.com',
			'Monto' => 1000.00,
			'Estado_Pago' => 'Pendiente',
			'tipo_pago' => 'Transferencia',
		]);
	}

	public function test_AgregarFacturaMontoDemasiadoGrande()
	{
		$payload = [
			'Monto' => 1000000.00, // Excede el máximo de 999999.99
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
		// Crear facturas usando datos similares a los seeders
		Factura::create([
			'email' => 'test@example.com',
			'Monto' => 1000.00,
			'Estado_Pago' => 'Pendiente',
			'tipo_pago' => 'Transferencia',
			'Archivo_Comprobante' => 'comprobantes/test1.pdf'
		]);
		Factura::create([
			'email' => 'test@example.com',
			'Monto' => 1100.00,
			'Estado_Pago' => 'Pagado',
			'tipo_pago' => 'Efectivo',
			'Archivo_Comprobante' => 'comprobantes/test2.pdf'
		]);
		// Factura de otro usuario que no debe aparecer
		Factura::create([
			'email' => 'otro@ejemplo.com',
			'Monto' => 1200.00,
			'Estado_Pago' => 'Pendiente',
			'tipo_pago' => 'Transferencia',
			'Archivo_Comprobante' => 'comprobantes/test3.pdf'
		]);

		$response = $this->getJson('/api/facturas');
		$response->assertStatus(200)
			->assertJsonCount(2, 'facturas')
			->assertJsonFragment(['Monto' => 1000.00])
			->assertJsonFragment(['Monto' => 1100.00])
			->assertJsonMissing(['Monto' => 1200.00]);
	}

	public function testDetalleFactura()
	{
		$factura = Factura::create([
			'email' => 'test@example.com',
			'Monto' => 1000.00,
			'Estado_Pago' => 'Pendiente',
			'tipo_pago' => 'Transferencia',
			'Archivo_Comprobante' => 'comprobantes/test_comprobante.pdf',
		]);

		$response = $this->getJson('/api/facturas/' . $factura->id);
		$response->assertStatus(200)
			->assertJsonFragment(['Monto' => 1000.00])
			->assertJsonStructure(['factura' => ['imagen_comprobante']]);
	}

	public function test_filtrarFacturasPorMesYAnio()
	{
		// Crear facturas con fechas específicas usando datos realistas
		$factura1 = Factura::create([
			'email' => 'test@example.com',
			'Monto' => 1000.00,
			'Estado_Pago' => 'Pendiente',
			'tipo_pago' => 'Transferencia',
		]);
		// Forzar la fecha de created_at después de la creación
		$factura1->created_at = '2024-01-15 10:00:00';
		$factura1->save();

		$factura2 = Factura::create([
			'email' => 'test@example.com',
			'Monto' => 1100.00,
			'Estado_Pago' => 'Pagado',
			'tipo_pago' => 'Efectivo',
		]);
		$factura2->created_at = '2024-02-10 10:00:00';
		$factura2->save();

		$factura3 = Factura::create([
			'email' => 'test@example.com',
			'Monto' => 1200.00,
			'Estado_Pago' => 'Pendiente',
			'tipo_pago' => 'Transferencia',
		]);
		$factura3->created_at = '2023-01-10 10:00:00';
		$factura3->save();

		$payload = [
			'mes' => 1,
			'anio' => 2024
		];
		$response = $this->postJson('/api/facturas/filtrar', $payload);
		
		$response->assertStatus(200)
			->assertJsonCount(1, 'facturas')
			->assertJsonFragment(['Monto' => 1000])
			->assertJsonMissing(['Monto' => 1100])
			->assertJsonMissing(['Monto' => 1200]);
	}

	public function test_CancelarFactura()
	{
		$factura = Factura::create([
			'email' => 'test@example.com',
			'Monto' => 1000.00,
			'Estado_Pago' => 'Pendiente',
			'tipo_pago' => 'Transferencia',
			'Archivo_Comprobante' => 'comprobantes/test_comprobante.pdf',
		]);

		$response = $this->deleteJson('/api/facturas/' . $factura->id);
		$response->assertStatus(200)
			->assertJsonFragment(['mensaje' => 'Factura cancelada correctamente']);

		$this->assertSoftDeleted('Factura', [
			'id' => $factura->id
		]);
	}
}
