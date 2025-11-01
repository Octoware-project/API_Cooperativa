<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlanTrabajo;

class PlanTrabajoController extends Controller
{
    public function index(Request $request)
    {
        try {
            $userData = $request->user;
            if (is_array($userData)) {
                $userData = (object) $userData;
            }
            $user = \App\Models\User::where('email', $userData->email)->first();
            
            if (!$user) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }
            
            $planes = $user->planTrabajos()->get();
            return response()->json($planes, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener planes de trabajo', 'mensaje' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $userData = $request->user;
            if (is_array($userData)) {
                $userData = (object) $userData;
            }
            $user = \App\Models\User::where('email', $userData->email)->first();
            
            if (!$user) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }
            
            $plan = PlanTrabajo::create([
                'user_id' => $user->id,
                'mes' => $request->input('mes'),
                'anio' => $request->input('anio'),
                'horas_requeridas' => $request->input('horas_requeridas'),
            ]);
            return response()->json($plan, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear plan de trabajo', 'mensaje' => $e->getMessage()], 500);
        }
    }

    public function progreso(Request $request, $id)
    {
        try {
            $userData = $request->user;
            if (is_array($userData)) {
                $userData = (object) $userData;
            }
            $user = \App\Models\User::where('email', $userData->email)->first();
            
            if (!$user) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }
            
            $plan = $user->planTrabajos()->findOrFail($id);
            
            $horasRegistradas = $user->horasMensuales()
                ->where('anio', $plan->anio)
                ->where('mes', $plan->mes)
                ->get();
            
            $horasReales = 0;
            $horasJustificadas = 0;
            
            foreach ($horasRegistradas as $hora) {
                if ($hora->Cantidad_Horas) {
                    $horasReales += $hora->Cantidad_Horas;
                }
                
                if ($hora->Monto_Compensario && $hora->Monto_Compensario > 0) {
                    if ($hora->horas_equivalentes_calculadas) {
                        $horasJustificadas += $hora->horas_equivalentes_calculadas;
                    } else {
                        $valorHora = $hora->valor_hora_al_momento ?: 1000;
                        $horasJustificadas += $hora->Monto_Compensario / $valorHora;
                    }
                }
            }
            
            $horasTotales = $horasReales + $horasJustificadas;
            
            $progreso = [
                'horas_requeridas' => $plan->horas_requeridas,
                'horas_cumplidas' => round($horasTotales, 2),
                'horas_reales' => round($horasReales, 2),
                'horas_justificadas' => round($horasJustificadas, 2),
                'porcentaje' => $plan->horas_requeridas > 0 ? round(($horasTotales / $plan->horas_requeridas) * 100, 2) : 0
            ];
            return response()->json($progreso, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener progreso del plan', 'mensaje' => $e->getMessage()], 500);
        }
    }

    public function dashboard(Request $request)
    {
        try {
            $userData = $request->user;
            if (is_array($userData)) {
                $userData = (object) $userData;
            }
            $user = \App\Models\User::where('email', $userData->email)->first();
            
            if (!$user) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }
            
            $planes = $user->planTrabajos()->get();
            
            $planesConProgreso = [];
            $totalPlanes = 0;
            $planesActivos = 0;
            $planesCompletados = 0;
            
            foreach ($planes as $plan) {
                $horasRegistradas = $user->horasMensuales()
                    ->where('anio', $plan->anio)
                    ->where('mes', $plan->mes)
                    ->get();
            
            $horasReales = 0;
            $horasJustificadas = 0;
            
            foreach ($horasRegistradas as $hora) {
                if ($hora->Cantidad_Horas) {
                    $horasReales += $hora->Cantidad_Horas;
                }
                
                if ($hora->Monto_Compensario && $hora->Monto_Compensario > 0) {
                    if ($hora->horas_equivalentes_calculadas) {
                        $horasJustificadas += $hora->horas_equivalentes_calculadas;
                    } else {
                        $valorHora = $hora->valor_hora_al_momento ?: 1000;
                        $horasJustificadas += $hora->Monto_Compensario / $valorHora;
                    }
                }
            }
            
            $horasTotales = $horasReales + $horasJustificadas;
            $porcentaje = $plan->horas_requeridas > 0 ? round(($horasTotales / $plan->horas_requeridas) * 100, 2) : 0;
            $completado = $porcentaje >= 100;
            
            $planConProgreso = [
                'id' => $plan->id,
                'mes' => $plan->mes,
                'anio' => $plan->anio,
                'horas_requeridas' => $plan->horas_requeridas,
                'progreso' => [
                    'horas_cumplidas' => round($horasTotales, 2),
                    'horas_reales' => round($horasReales, 2),
                    'horas_justificadas' => round($horasJustificadas, 2),
                    'porcentaje' => $porcentaje,
                    'completado' => $completado
                ]
            ];
            
            $planesConProgreso[] = $planConProgreso;
            
            $totalPlanes++;
            if ($completado) {
                $planesCompletados++;
            } else if ($porcentaje > 0) {
                $planesActivos++;
            }
        }
        
            $response = [
                'planes' => $planesConProgreso,
                'estadisticas' => [
                    'total_planes' => $totalPlanes,
                    'planes_activos' => $planesActivos,
                    'planes_completados' => $planesCompletados
                ],
                'meta' => [
                    'query_time_ms' => round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2)
                ]
            ];
            
            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener dashboard', 'mensaje' => $e->getMessage()], 500);
        }
    }
}
