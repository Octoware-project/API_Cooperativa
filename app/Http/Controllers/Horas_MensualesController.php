<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Horas_Mensuales;

class Horas_MensualesController extends Controller
{
 
    private function getAuthenticatedUser(Request $request)
    {
        $user = $request->user;
        if (is_array($user)) {
            $user = (object) $user;
        }
        
        return $user;
    }

 
    public function index(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            $horas = Horas_Mensuales::where('email', $user->email)->get();
            return response()->json(['horas' => $horas], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener horas mensuales', 'mensaje' => $e->getMessage()], 500);
        }
    }
    public function sumarHorasUltimoMes(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            $now = now();
            $inicio = $now->copy()->startOfMonth();
            $fin = $now->copy()->endOfMonth();
            
            $total = Horas_Mensuales::where('email', $user->email)
                ->whereBetween('created_at', [$inicio, $fin])
                ->sum('Cantidad_Horas');
                
            return response()->json(['total_horas_ultimo_mes' => $total], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al sumar horas del último mes', 'mensaje' => $e->getMessage()], 500);
        }
    }


    public function calcularHorasRegistradas(Request $request) 
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            $mes = $request->input('mes');
            $anio = $request->input('anio', now()->year);
            
            if (!$mes) {
                return response()->json(['error' => 'El parámetro mes es requerido'], 400);
            }

            $registros = Horas_Mensuales::where('email', $user->email)
                ->where('mes', $mes)
                ->where('anio', $anio)
                ->get();
            
            $horasReales = $registros->sum('Cantidad_Horas') ?? 0;
            $horasEquivalentes = $registros->sum(function($registro) {
                return $registro->getHorasEquivalentes();
            });
            $horasJustificadas = $horasEquivalentes - $horasReales;
                
            return response()->json([
                'total_horas' => $horasEquivalentes,
                'horas_reales' => $horasReales,
                'horas_justificadas' => $horasJustificadas,
                'detalle_registros' => $registros->map(function($registro) {
                    return [
                        'id' => $registro->id,
                        'fecha' => $registro->dia . '/' . $registro->mes . '/' . $registro->anio,
                        'horas_reales' => $registro->Cantidad_Horas ?? 0,
                        'horas_equivalentes' => $registro->getHorasEquivalentes(),
                        'monto_compensario' => $registro->Monto_Compensario,
                        'es_justificacion' => $registro->Monto_Compensario > 0,
                        'valor_hora_usado' => $registro->valor_hora_al_momento
                    ];
                })
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al calcular horas registradas', 'mensaje' => $e->getMessage()], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);

            $request->validate([
                'Cantidad_Horas' => 'required|numeric|min:0|max:24',
                'dia' => 'nullable|integer|min:1|max:31',
                'mes' => 'nullable|integer|min:1|max:12',
                'anio' => 'nullable|integer|min:2000|max:2099',
                'Monto_Compensario' => 'nullable|numeric|min:0',
                'Motivo_Falla' => 'nullable|string|max:255',
                'Tipo_Justificacion' => 'nullable|string|max:255'
            ]);

            $horasMensuales = new Horas_Mensuales();
            $horasMensuales->email = $user->email;
            $horasMensuales->anio = $request->input('anio', now()->year);
            $horasMensuales->mes = $request->input('mes', now()->month);
            $horasMensuales->dia = $request->input('dia', now()->day);
            $horasMensuales->Cantidad_Horas = $request->input('Cantidad_Horas');
            $horasMensuales->Monto_Compensario = $request->input('Monto_Compensario');
            $horasMensuales->Motivo_Falla = $request->input('Motivo_Falla');
            $horasMensuales->Tipo_Justificacion = $request->input('Tipo_Justificacion');
            
            $horasMensuales->save();
            
            $horasMensuales->calcularYFijarHorasEquivalentes();
            $horasMensuales->save();
            
            return response()->json([
                'mensaje' => 'Horas agregadas exitosamente',
                'data' => $horasMensuales
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al agregar horas', 'mensaje' => $e->getMessage()], 500);
        }
    }

    public function agregarJustificacion(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser($request);

            $request->validate([
                'Motivo_Falla' => 'required|string|max:255',
                'Tipo_Justificacion' => 'required|string|max:255',
                'dia' => 'nullable|integer|min:1|max:31',
                'mes' => 'nullable|integer|min:1|max:12',
                'anio' => 'nullable|integer|min:2000|max:2099',
                'Monto_Compensario' => 'nullable|numeric|min:0'
            ]);

            $horasMensuales = new Horas_Mensuales();
            $horasMensuales->email = $user->email;
            $horasMensuales->anio = $request->input('anio', now()->year);
            $horasMensuales->mes = $request->input('mes', now()->month);
            $horasMensuales->dia = $request->input('dia', now()->day);
            $horasMensuales->Cantidad_Horas = null;
            $horasMensuales->Monto_Compensario = $request->input('Monto_Compensario');
            $horasMensuales->Motivo_Falla = $request->input('Motivo_Falla');
            $horasMensuales->Tipo_Justificacion = $request->input('Tipo_Justificacion');
            
            $horasMensuales->save();
            
            $horasMensuales->calcularYFijarHorasEquivalentes();
            $horasMensuales->save();
            
            return response()->json([
                'mensaje' => 'Justificación agregada exitosamente',
                'data' => $horasMensuales
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al agregar justificación', 'mensaje' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = $this->getAuthenticatedUser($request);

            $horasMensuales = Horas_Mensuales::where('id', $id)
                ->where('email', $user->email)
                ->first();

            if (!$horasMensuales) {
                return response()->json(['error' => 'Registro no encontrado o no autorizado'], 404);
            }

            $request->validate([
                'Cantidad_Horas' => 'nullable|numeric|min:0|max:24',
                'dia' => 'nullable|integer|min:1|max:31',
                'mes' => 'nullable|integer|min:1|max:12',
                'anio' => 'nullable|integer|min:2000|max:2099',
                'Monto_Compensario' => 'nullable|numeric|min:0',
                'Motivo_Falla' => 'nullable|string|max:255',
                'Tipo_Justificacion' => 'nullable|string|max:255'
            ]);

            if ($request->has('dia')) {
                $horasMensuales->dia = $request->input('dia');
            }
            if ($request->has('mes')) {
                $horasMensuales->mes = $request->input('mes');
            }
            if ($request->has('anio')) {
                $horasMensuales->anio = $request->input('anio');
            }
            if ($request->has('Cantidad_Horas')) {
                $horasMensuales->Cantidad_Horas = $request->input('Cantidad_Horas');
            }
            if ($request->has('Monto_Compensario')) {
                $horasMensuales->Monto_Compensario = $request->input('Monto_Compensario');
            }
            if ($request->has('Motivo_Falla')) {
                $horasMensuales->Motivo_Falla = $request->input('Motivo_Falla');
            }
            if ($request->has('Tipo_Justificacion')) {
                $horasMensuales->Tipo_Justificacion = $request->input('Tipo_Justificacion');
            }
            
            $horasMensuales->save();
            
            return response()->json([
                'mensaje' => 'Registro actualizado exitosamente',
                'data' => $horasMensuales
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar registro', 'mensaje' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $user = $this->getAuthenticatedUser($request);

            $horasMensuales = Horas_Mensuales::where('id', $id)
                ->where('email', $user->email)
                ->first();

            if (!$horasMensuales) {
                return response()->json(['error' => 'Registro no encontrado o no autorizado'], 404);
            }

            if ($horasMensuales->created_at->diffInHours(now()) >= 24) {
                return response()->json([
                    'error' => 'Solo se pueden eliminar registros con menos de 24 horas de creados'
                ], 403);
            }

            $horasMensuales->delete();
            
            return response()->json([
                'mensaje' => "Registro de horas $id eliminado exitosamente"
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar registro', 'mensaje' => $e->getMessage()], 500);
        }
    }


    public function show(Request $request, $id)
    {
        try {
            $user = $this->getAuthenticatedUser($request);

            $horasMensuales = Horas_Mensuales::where('id', $id)
                ->where('email', $user->email)
                ->first();

            if (!$horasMensuales) {
                return response()->json(['error' => 'Registro no encontrado o no autorizado'], 404);
            }

            return response()->json([
                'data' => $horasMensuales
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener registro', 'mensaje' => $e->getMessage()], 500);
        }
    }
}