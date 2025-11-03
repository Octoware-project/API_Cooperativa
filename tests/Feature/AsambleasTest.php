<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\JuntaAsamblea;

class AsambleasTest extends TestCase
{
    public function test_listarTodasLasAsambleas()
    {
        $response = $this->getJson('/api/asambleas');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'lugar',
                        'fecha',
                        'fecha_raw',
                        'detalle',
                        'es_futura',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'total'
            ])
            ->assertJson(['success' => true]);
        
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    public function test_obtenerDetalleAsamblea()
    {
        $asamblea = JuntaAsamblea::first();
        $this->assertNotNull($asamblea);
        
        $response = $this->getJson('/api/asambleas/' . $asamblea->id);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'lugar',
                    'fecha',
                    'fecha_raw',
                    'detalle',
                    'es_futura'
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $asamblea->id,
                    'lugar' => $asamblea->lugar
                ]
            ]);
    }

    public function test_listarAsambleasFuturas()
    {
        $response = $this->getJson('/api/asambleas-futuras');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'total'
            ])
            ->assertJson(['success' => true]);
        
        $data = $response->json('data');
        
        foreach ($data as $asamblea) {
            $this->assertTrue($asamblea['es_futura']);
            $this->assertGreaterThanOrEqual(now()->format('Y-m-d'), $asamblea['fecha_raw']);
        }
    }

    public function test_listarAsambleasPasadas()
    {
        $response = $this->getJson('/api/asambleas-pasadas');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'total'
            ])
            ->assertJson(['success' => true]);
        
        $data = $response->json('data');
        
        foreach ($data as $asamblea) {
            $this->assertFalse($asamblea['es_futura']);
            $this->assertLessThan(now()->format('Y-m-d'), $asamblea['fecha_raw']);
        }
    }

    public function test_asambleaNoExistente()
    {
        $response = $this->getJson('/api/asambleas/99999');
        
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Asamblea no encontrada'
            ]);
    }
}
