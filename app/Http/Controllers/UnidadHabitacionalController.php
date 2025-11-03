<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UnidadHabitacional;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UnidadHabitacionalController extends Controller
{

    public function miUnidad(Request $request)
    {
        try {
            $userEmail = $request->user['email'];
            $user = User::where('email', $userEmail)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                    'data' => null
                ], 404);
            }
            
            $persona = Persona::where('user_id', $user->id)->first();
            
            if (!$persona) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron datos de persona para el usuario.',
                    'data' => null
                ], 404);
            }
            
            if (!$persona->unidad_habitacional_id) {
                return response()->json([
                    'success' => true,
                    'message' => 'El usuario no tiene una unidad habitacional asignada.',
                    'data' => null
                ], 200);
            }
            
            $unidad = UnidadHabitacional::with(['personas.user'])
                ->findOrFail($persona->unidad_habitacional_id);
            
            $response = [
                'id' => $unidad->id,
                'numero_departamento' => $unidad->numero_departamento,
                'piso' => $unidad->piso,
                'estado' => $unidad->estado,
                'nombre_completo' => $unidad->nombre_completo,
                'fecha_asignacion' => $persona->fecha_asignacion_unidad,
                'total_residentes' => $unidad->personas->count(),
                'esta_ocupada' => $unidad->esta_ocupada,
                'created_at' => $unidad->created_at,
                'updated_at' => $unidad->updated_at,
            ];
            
            return response()->json([
                'success' => true,
                'message' => 'Información de la unidad obtenida exitosamente.',
                'data' => $response
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la información de la unidad.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function residentesDeUnidad(Request $request)
    {
        try {
            $userEmail = $request->user['email'];
            $user = User::where('email', $userEmail)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                    'data' => []
                ], 404);
            }
            
            $persona = Persona::where('user_id', $user->id)->first();
            
            if (!$persona) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron datos de persona para el usuario.',
                    'data' => []
                ], 404);
            }
            
            if (!$persona->unidad_habitacional_id) {
                return response()->json([
                    'success' => true,
                    'message' => 'El usuario no tiene una unidad habitacional asignada.',
                    'data' => []
                ], 200);
            }
            
            $residentes = Persona::with('user')
                ->where('unidad_habitacional_id', $persona->unidad_habitacional_id)
                ->get();
            
            $userId = $user->id;
            $residentesFormateados = $residentes->map(function ($residente) use ($userId) {
                return [
                    'id' => $residente->id,
                    'user_id' => $residente->user_id,
                    'name' => $residente->name,
                    'apellido' => $residente->apellido,
                    'nombre_completo' => $residente->name . ' ' . $residente->apellido,
                    'CI' => $residente->CI,
                    'telefono' => $residente->telefono,
                    'direccion' => $residente->direccion,
                    'estadoCivil' => $residente->estadoCivil,
                    'genero' => $residente->genero,
                    'fechaNacimiento' => $residente->fechaNacimiento,
                    'ocupacion' => $residente->ocupacion,
                    'nacionalidad' => $residente->nacionalidad,
                    'estadoRegistro' => $residente->estadoRegistro,
                    'fecha_asignacion_unidad' => $residente->fecha_asignacion_unidad,
                    'fecha_aceptacion' => $residente->fecha_aceptacion,
                    'email' => $residente->user ? $residente->user->email : null,
                    'es_usuario_actual' => $residente->user_id === $userId,
                ];
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Residentes obtenidos exitosamente.',
                'data' => $residentesFormateados,
                'total_residentes' => $residentesFormateados->count()
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los residentes de la unidad.',
                'error' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}