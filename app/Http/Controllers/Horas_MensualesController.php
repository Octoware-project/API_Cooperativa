<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Horas_Mensuales;

class Horas_MensualesController extends Controller
{
    /**
     * Obtiene los datos del usuario autenticado desde el request
     */
    private function getAuthenticatedUser(Request $request)
    {
        $user = $request->user;
        if (is_array($user)) {
            $user = (object) $user;
        }
        
        return $user;
    }

    /**
     * Lista todos los registros de horas del usuario autenticado
     */
    public function index(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        $horas = Horas_Mensuales::where('email', $user->email)->get();
        return response()->json(['horas' => $horas], 200);
    }
    /**
     * Suma todas las horas del mes actual para el usuario autenticado
     */
    public function sumarHorasUltimoMes(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        $now = now();
        $inicio = $now->copy()->startOfMonth();
        $fin = $now->copy()->endOfMonth();
        
        $total = Horas_Mensuales::where('email', $user->email)
            ->whereBetween('created_at', [$inicio, $fin])
            ->sum('Cantidad_Horas');
            
        return response()->json(['total_horas_ultimo_mes' => $total], 200);
    }

    /**
     * Calcula las horas registradas por mes y año (incluyendo horas equivalentes)
     */
    public function calcularHorasRegistradas(Request $request) 
    {
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
        
        // Calcular horas reales y equivalentes
        $horasReales = $registros->sum('Cantidad_Horas') ?? 0;
        $horasEquivalentes = $registros->sum(function($registro) {
            return $registro->getHorasEquivalentes();
        });
        $horasJustificadas = $horasEquivalentes - $horasReales;
            
        return response()->json([
            'total_horas' => $horasEquivalentes, // Total (reales + justificadas)
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
    }

    /**
     * Agrega un nuevo registro de horas
     */
    public function store(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);

        // Validación de datos requeridos
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
        
        // NUEVO: Calcular y fijar horas equivalentes al momento de guardar
        $horasMensuales->calcularYFijarHorasEquivalentes();
        $horasMensuales->save();
        
        return response()->json([
            'mensaje' => 'Horas agregadas exitosamente',
            'data' => $horasMensuales
        ], 201);
    }

    /**
     * Agrega una justificación (sin horas)
     */
    public function agregarJustificacion(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);

        // Validación de datos requeridos para justificación
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
        $horasMensuales->Cantidad_Horas = null; // Sin horas para justificaciones
        $horasMensuales->Monto_Compensario = $request->input('Monto_Compensario');
        $horasMensuales->Motivo_Falla = $request->input('Motivo_Falla');
        $horasMensuales->Tipo_Justificacion = $request->input('Tipo_Justificacion');
        
        $horasMensuales->save();
        
        // NUEVO: Calcular y fijar horas equivalentes
        $horasMensuales->calcularYFijarHorasEquivalentes();
        $horasMensuales->save();
        
        return response()->json([
            'mensaje' => 'Justificación agregada exitosamente',
            'data' => $horasMensuales
        ], 201);
    }

    /**
     * Edita un registro de horas existente (solo del usuario autenticado)
     */
    public function update(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser($request);

        // Buscar el registro solo si pertenece al usuario autenticado
        $horasMensuales = Horas_Mensuales::where('id', $id)
            ->where('email', $user->email)
            ->first();

        if (!$horasMensuales) {
            return response()->json(['error' => 'Registro no encontrado o no autorizado'], 404);
        }

        // Validación de datos
        $request->validate([
            'Cantidad_Horas' => 'nullable|numeric|min:0|max:24',
            'dia' => 'nullable|integer|min:1|max:31',
            'mes' => 'nullable|integer|min:1|max:12',
            'anio' => 'nullable|integer|min:2000|max:2099',
            'Monto_Compensario' => 'nullable|numeric|min:0',
            'Motivo_Falla' => 'nullable|string|max:255',
            'Tipo_Justificacion' => 'nullable|string|max:255'
        ]);

        // Actualizar solo los campos enviados
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
    }

    /**
     * Elimina un registro de horas (solo si tiene menos de 24 horas de creado)
     */
    public function destroy(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser($request);

        $horasMensuales = Horas_Mensuales::where('id', $id)
            ->where('email', $user->email)
            ->first();

        if (!$horasMensuales) {
            return response()->json(['error' => 'Registro no encontrado o no autorizado'], 404);
        }

        // Validar antigüedad menor a 24 horas
        if ($horasMensuales->created_at->diffInHours(now()) >= 24) {
            return response()->json([
                'error' => 'Solo se pueden eliminar registros con menos de 24 horas de creados'
            ], 403);
        }

        $horasMensuales->delete();
        
        return response()->json([
            'mensaje' => "Registro de horas $id eliminado exitosamente"
        ], 200);
    }

    /**
     * Muestra el detalle de un registro específico (solo del usuario autenticado)
     */
    public function show(Request $request, $id)
    {
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
    }

    /**
     * ENDPOINT OPTIMIZADO: Dashboard completo con todas las horas, progreso y plan en una sola respuesta
     */
    public function dashboardHoras(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        $mes = $request->input('mes', now()->month);
        $anio = $request->input('anio', now()->year);
        
        try {
            // 1. Una sola query para obtener todos los registros del mes
            $registros = Horas_Mensuales::where('email', $user->email)
                ->where('mes', $mes)
                ->where('anio', $anio)
                ->select([
                    'id', 'dia', 'Cantidad_Horas', 'Monto_Compensario',
                    'horas_equivalentes_calculadas', 'valor_hora_al_momento',
                    'created_at', 'Motivo_Falla', 'Tipo_Justificacion'
                ])
                ->orderBy('dia', 'asc')
                ->get();

            // 2. Obtener valor hora actual (con cache)
            $valorHoraActual = \App\Models\ConfiguracionHoras::getValorActual();
            
            // 3. Calcular todo en PHP (más eficiente que múltiples queries)
            $horasReales = 0;
            $horasJustificadas = 0;
            $registrosFormateados = [];
            
            foreach ($registros as $registro) {
                $hReales = $registro->Cantidad_Horas ?? 0;
                $hJustif = 0;
                
                // Calcular horas justificadas si hay monto
                if ($registro->Monto_Compensario > 0) {
                    $valorUsado = $registro->valor_hora_al_momento ?? $valorHoraActual;
                    if ($valorUsado > 0) {
                        $hJustif = $registro->Monto_Compensario / $valorUsado;
                    }
                }
                
                $horasReales += $hReales;
                $horasJustificadas += $hJustif;
                
                // Determinar tipo de registro
                $tipo = $this->determinarTipoRegistro($hReales, $hJustif);
                
                // Verificar si puede cancelar (menos de 24 horas)
                $puedeCancelar = $registro->created_at->diffInHours(now()) < 24;
                
                $registrosFormateados[] = [
                    'id' => $registro->id,
                    'fecha' => sprintf('%02d/%02d/%d', $registro->dia, $mes, $anio),
                    'horas_reales' => $hReales,
                    'horas_justificadas' => round($hJustif, 2),
                    'total_horas' => round($hReales + $hJustif, 2),
                    'tipo' => $tipo['key'],
                    'tipo_nombre' => $tipo['nombre'],
                    'tipo_descripcion' => $tipo['descripcion'],
                    'puede_cancelar' => $puedeCancelar,
                    'descripcion' => $registro->Motivo_Falla,
                    'justificacion_tipo' => $registro->Tipo_Justificacion,
                    'monto_compensario' => $registro->Monto_Compensario
                ];
            }
            
            // 4. Obtener plan de trabajo en la misma consulta (si existe)
            $planData = null;
            $progresoData = null;
            
            // Buscar plan usando el user_id si tenemos la relación
            $userModel = \App\Models\User::where('email', $user->email)->first();
            if ($userModel) {
                $plan = \App\Models\PlanTrabajo::where('user_id', $userModel->id)
                    ->where('mes', $mes)
                    ->where('anio', $anio)
                    ->first();
                
                if ($plan) {
                    $totalHoras = $horasReales + $horasJustificadas;
                    $progresoPorcentaje = $plan->horas_requeridas > 0 
                        ? min(($totalHoras / $plan->horas_requeridas) * 100, 100) 
                        : 0;
                    
                    $planData = [
                        'id' => $plan->id,
                        'horas_requeridas' => $plan->horas_requeridas,
                        'mes' => $plan->mes,
                        'anio' => $plan->anio
                    ];
                    
                    $progresoData = [
                        'horas_requeridas' => $plan->horas_requeridas,
                        'horas_cumplidas' => round($totalHoras, 2),
                        'horas_reales' => round($horasReales, 2),
                        'horas_justificadas' => round($horasJustificadas, 2),
                        'porcentaje' => round($progresoPorcentaje, 2),
                        'completado' => $progresoPorcentaje >= 100
                    ];
                }
            }
            
            return response()->json([
                'registros' => $registrosFormateados,
                'resumen' => [
                    'horas_reales' => round($horasReales, 2),
                    'horas_justificadas' => round($horasJustificadas, 2),
                    'total_horas' => round($horasReales + $horasJustificadas, 2),
                    'total_registros' => count($registros)
                ],
                'plan' => $planData,
                'progreso' => $progresoData,
                'mes' => (int)$mes,
                'anio' => (int)$anio,
                'valor_hora_actual' => $valorHoraActual
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener datos del dashboard',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Determinar tipo de registro basado en horas
     */
    private function determinarTipoRegistro($horasReales, $horasJustificadas)
    {
        if ($horasReales > 0 && $horasJustificadas > 0) {
            return [
                'key' => 'mixtas',
                'nombre' => 'Horas Mixtas',
                'descripcion' => 'Reales + Justificadas'
            ];
        } elseif ($horasJustificadas > 0) {
            return [
                'key' => 'justificacion',
                'nombre' => 'Justificación',
                'descripcion' => 'Horas justificadas'
            ];
        } else {
            return [
                'key' => 'horas-reales',
                'nombre' => 'Horas Trabajadas',
                'descripcion' => 'Horas de trabajo'
            ];
        }
    }
}