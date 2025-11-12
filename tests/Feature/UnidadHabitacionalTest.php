<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Persona;
use App\Models\UnidadHabitacional;

class UnidadHabitacionalTest extends TestCase
{
    public function test_obtenerMiUnidad()
    {
        $response = $this->getJson('/api/mi-unidad');
        
        $response->assertStatus(200)
            ->assertJson(['success' => true]);
        
        $data = $response->json('data');
        
        if ($data !== null) {
            $this->assertArrayHasKey('id', $data);
            $this->assertArrayHasKey('numero_departamento', $data);
            $this->assertArrayHasKey('piso', $data);
            $this->assertArrayHasKey('estado', $data);
            $this->assertArrayHasKey('nombre_completo', $data);
            $this->assertArrayHasKey('total_residentes', $data);
            $this->assertArrayHasKey('esta_ocupada', $data);
        }
    }

    public function test_obtenerResidentesDeUnidad()
    {
        $response = $this->getJson('/api/mi-unidad/residentes');
        
        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);
        
        $residentes = $response->json('data');
        $responseData = $response->json();
        
        if (count($residentes) > 0 && isset($responseData['total_residentes'])) {
            $this->assertArrayHasKey('total_residentes', $responseData);
            $this->assertArrayHasKey('id', $residentes[0]);
            $this->assertArrayHasKey('user_id', $residentes[0]);
            $this->assertArrayHasKey('name', $residentes[0]);
            $this->assertArrayHasKey('apellido', $residentes[0]);
            $this->assertArrayHasKey('nombre_completo', $residentes[0]);
            $this->assertArrayHasKey('CI', $residentes[0]);
            $this->assertArrayHasKey('telefono', $residentes[0]);
            $this->assertArrayHasKey('email', $residentes[0]);
            $this->assertArrayHasKey('es_usuario_actual', $residentes[0]);
            
            $usuarioActualEncontrado = false;
            foreach ($residentes as $residente) {
                if ($residente['email'] === 'user@test.com') {
                    $this->assertTrue($residente['es_usuario_actual']);
                    $usuarioActualEncontrado = true;
                }
            }
            
            $this->assertTrue($usuarioActualEncontrado);
        } else {
            $this->assertEmpty($residentes);
        }
    }

    public function test_usuarioSinUnidadAsignada()
    {
        $user = User::where('email', 'user@test.com')->first();
        $persona = $user->persona;
        $unidadOriginal = $persona->unidad_habitacional_id;
        
        $persona->update(['unidad_habitacional_id' => null]);
        
        $response = $this->getJson('/api/mi-unidad');
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => null
            ]);
        
        $persona->update(['unidad_habitacional_id' => $unidadOriginal]);
    }
}
