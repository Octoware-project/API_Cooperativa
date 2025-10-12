<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Persona;

class UserController extends Controller
{
    /**
     * Completar datos de la persona autenticada
     */
    public function completarDatos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telefono' => 'required|string|max:30',
            'direccion' => 'required|string|max:255',
            'estadoCivil' => 'required|string|max:50',
            'genero' => 'required|string|max:50',
            'fechaNacimiento' => 'required|date',
            'ocupacion' => 'required|string|max:100',
            'nacionalidad' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Obtener datos del usuario autenticado desde el middleware
            $userEmail = $request->user['email'];
            
            // Buscar el usuario en la base de datos de usuarios
            $user = User::where('email', $userEmail)->first();
            if (!$user) {
                return response()->json(['message' => 'Usuario no encontrado'], 404);
            }

            $persona = $user->persona;
            if (!$persona) {
                return response()->json(['message' => 'No se encontró la persona asociada'], 404);
            }

            // Actualizar datos (solo los permitidos)
            $persona->update([
                'telefono' => $request->input('telefono'),
                'direccion' => $request->input('direccion'),
                'estadoCivil' => $request->input('estadoCivil'),
                'genero' => $request->input('genero'),
                'fechaNacimiento' => $request->input('fechaNacimiento'),
                'ocupacion' => $request->input('ocupacion'),
                'nacionalidad' => $request->input('nacionalidad'),
                'estadoRegistro' => 'Aceptado',
            ]);

            return response()->json([
                'message' => 'Datos completados correctamente',
                'persona' => $persona
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Editar datos de persona autenticada (solo campos editables)
     */
    public function editarDatosPersona(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telefono' => 'required|string|max:30',
            'direccion' => 'required|string|max:255',
            'estadoCivil' => 'required|string|max:50',
            'genero' => 'required|string|max:50',
            'fechaNacimiento' => 'required|date',
            'ocupacion' => 'required|string|max:100',
            'nacionalidad' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Obtener datos del usuario autenticado desde el middleware
            $userEmail = $request->user['email'];
            
            // Buscar el usuario en la base de datos de usuarios
            $user = User::where('email', $userEmail)->first();
            if (!$user) {
                return response()->json(['message' => 'Usuario no encontrado'], 404);
            }

            $persona = $user->persona;
            if (!$persona) {
                return response()->json(['message' => 'No se encontró la persona asociada'], 404);
            }

            // Actualizar datos (solo los permitidos)
            $persona->update([
                'telefono' => $request->input('telefono'),
                'direccion' => $request->input('direccion'),
                'estadoCivil' => $request->input('estadoCivil'),
                'genero' => $request->input('genero'),
                'fechaNacimiento' => $request->input('fechaNacimiento'),
                'ocupacion' => $request->input('ocupacion'),
                'nacionalidad' => $request->input('nacionalidad'),
            ]);

            return response()->json([
                'message' => 'Datos personales actualizados correctamente',
                'persona' => $persona
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos del usuario autenticado
     */
    public function obtenerDatosUsuario(Request $request)
    {
        try {
            // Verificar que el middleware haya pasado los datos del usuario
            if (!isset($request->user) || !isset($request->user['email'])) {
                return response()->json(['message' => 'Datos de usuario no disponibles'], 401);
            }
            
            // Obtener datos del usuario autenticado desde el middleware
            $userEmail = $request->user['email'];
            
            // Buscar el usuario en la base de datos
            $user = User::where('email', $userEmail)->with('persona')->first();
            if (!$user) {
                return response()->json(['message' => 'Usuario no encontrado'], 404);
            }

            // Construir respuesta optimizada con solo los campos necesarios
            $response = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
                ],
                'persona' => $user->persona ? [
                    'id' => $user->persona->id,
                    'name' => $user->persona->name,
                    'apellido' => $user->persona->apellido,
                    'CI' => $user->persona->CI,
                    'telefono' => $user->persona->telefono,
                    'direccion' => $user->persona->direccion,
                    'estadoCivil' => $user->persona->estadoCivil,
                    'genero' => $user->persona->genero,
                    'fechaNacimiento' => $user->persona->fechaNacimiento,
                    'ocupacion' => $user->persona->ocupacion,
                    'nacionalidad' => $user->persona->nacionalidad,
                    'estadoRegistro' => $user->persona->estadoRegistro,
                ] : null
            ];
            
            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar contraseña del usuario autenticado
     */
    public function cambiarContrasena(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:6',
            'password_confirmation' => 'required|string|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Obtener datos del usuario autenticado desde el middleware
            $userEmail = $request->user['email'];
            
            // Buscar el usuario en la base de datos de usuarios
            $user = User::where('email', $userEmail)->first();
            if (!$user) {
                return response()->json(['message' => 'Usuario no encontrado'], 404);
            }

            // Verificar que la contraseña actual sea correcta
            if (!Hash::check($request->input('current_password'), $user->password)) {
                return response()->json([
                    'message' => 'La contraseña actual es incorrecta',
                    'errors' => ['current_password' => ['La contraseña actual no es válida']]
                ], 422);
            }

            // Actualizar la contraseña
            $user->update([
                'password' => Hash::make($request->input('password'))
            ]);

            return response()->json([
                'message' => 'Contraseña actualizada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}