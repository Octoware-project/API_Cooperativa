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
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'numero_departamento',
                    'piso',
                    'estado',
                    'nombre_completo',
                    'total_residentes',
                    'esta_ocupada'
                ]
            ])
            ->assertJson(['success' => true]);
    }

    public function test_obtenerResidentesDeUnidad()
    {
        $response = $this->getJson('/api/mi-unidad/residentes');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'name',
                        'apellido',
                        'nombre_completo',
                        'CI',
                        'telefono',
                        'email',
                        'es_usuario_actual'
                    ]
                ],
                'total_residentes'
            ])
            ->assertJson(['success' => true]);
        
        $residentes = $response->json('data');
        $this->assertGreaterThanOrEqual(1, count($residentes));
        
        $usuarioActualEncontrado = false;
        foreach ($residentes as $residente) {
            if ($residente['email'] === 'user@test.com') {
                $this->assertTrue($residente['es_usuario_actual']);
                $usuarioActualEncontrado = true;
            }
        }
        
        $this->assertTrue($usuarioActualEncontrado);
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
