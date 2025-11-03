<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Persona;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    public function test_obtenerDatosUsuario()
    {
        $response = $this->getJson('/api/datos-usuario');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at'
                ],
                'persona',
                'unidad_habitacional'
            ]);
        
        $data = $response->json();
        $this->assertEquals('user@test.com', $data['user']['email']);
        $this->assertNotNull($data['persona']);
    }

    public function test_completarDatos()
    {
        $payload = [
            'telefono' => '+59899123456',
            'direccion' => 'Calle Test 123',
            'estadoCivil' => 'Soltero',
            'genero' => 'Masculino',
            'fechaNacimiento' => '1990-01-15',
            'ocupacion' => 'Desarrollador',
            'nacionalidad' => 'Uruguayo'
        ];
        
        $response = $this->postJson('/api/completar-datos', $payload);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'persona' => [
                    'telefono',
                    'direccion',
                    'estadoCivil',
                    'genero',
                    'fechaNacimiento',
                    'ocupacion',
                    'nacionalidad',
                    'estadoRegistro'
                ]
            ])
            ->assertJson([
                'message' => 'Datos completados correctamente',
                'persona' => [
                    'estadoRegistro' => 'Aceptado'
                ]
            ]);
    }

    public function test_editarDatosPersona()
    {
        $payload = [
            'telefono' => '+59899111222',
            'direccion' => 'Nueva Direccion 456',
            'estadoCivil' => 'Casado',
            'genero' => 'Masculino',
            'fechaNacimiento' => '1990-05-20',
            'ocupacion' => 'Ingeniero',
            'nacionalidad' => 'Uruguayo'
        ];
        
        $response = $this->postJson('/api/editar-datos-persona', $payload);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'persona'
            ])
            ->assertJson([
                'message' => 'Datos personales actualizados correctamente'
            ]);
    }

    public function test_completarDatosConDatosInvalidos()
    {
        $payload = [
            'telefono' => '',
            'direccion' => '',
            'estadoCivil' => '',
        ];
        
        $response = $this->postJson('/api/completar-datos', $payload);
        
        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors'
            ]);
    }

    public function test_cambiarContrasena()
    {
        $user = User::where('email', 'user@test.com')->first();
        $this->assertNotNull($user);
        
        $user->password = Hash::make('oldpassword');
        $user->save();
        
        $payload = [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];
        
        $response = $this->postJson('/api/cambiar-contrasena', $payload);
        
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Contraseña actualizada correctamente'
            ]);
        
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
        
        $user->password = Hash::make('password');
        $user->save();
    }

    public function test_cambiarContrasenaConContrasenaActualIncorrecta()
    {
        $payload = [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];
        
        $response = $this->postJson('/api/cambiar-contrasena', $payload);
        
        $response->assertStatus(422)
            ->assertJson([
                'message' => 'La contraseña actual es incorrecta'
            ]);
    }

    public function test_cambiarContrasenaConConfirmacionDiferente()
    {
        $payload = [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword'
        ];
        
        $response = $this->postJson('/api/cambiar-contrasena', $payload);
        
        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors'
            ]);
    }
}
