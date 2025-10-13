<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlanTrabajo;
use Illuminate\Support\Facades\DB;

class PlanTrabajoController extends Controller
{
    // Endpoint original sin cambios para mantener compatibilidad
    public function index(Request $request)
    {
        $userData = $request->user;
        if (is_array($userData)) {
            $userData = (object) $userData;
        }
        $user = \App\Models\User::where('email', $userData->email)->first();
        $planes = $user->planTrabajos()->get();
        return response()->json($planes, 200);
    }

    // NUEVO ENDPOINT OPTIMIZADO: Lista planes con progreso agregado
    public function indexWithProgress(Request $request)
    {
        $userData = $request->user;
        if (is_array($userData)) {
            $userData = (object) $userData;
        }
        $user = \App\Models\User::where('email', $userData->email)->first();
        
        // Consulta optimizada que obtiene planes y progreso en una sola operación
        $planesConProgreso = DB::select("
            SELECT 
                p.id,
                p.mes,
                p.anio,
                p.horas_requeridas,
                p.created_at,
                p.updated_at,
                COALESCE(SUM(h.Cantidad_Horas), 0) as horas_reales,
                COALESCE(SUM(
                    CASE 
                        WHEN h.horas_equivalentes_calculadas IS NOT NULL 
                        THEN h.horas_equivalentes_calculadas - COALESCE(h.Cantidad_Horas, 0)
                        WHEN h.Monto_Compensario > 0 
                        THEN h.Monto_Compensario / COALESCE(h.valor_hora_al_momento, 1000)
                        ELSE 0
                    END
                ), 0) as horas_justificadas,
                COALESCE(SUM(
                    COALESCE(h.Cantidad_Horas, 0) + 
                    CASE 
                        WHEN h.horas_equivalentes_calculadas IS NOT NULL 
                        THEN h.horas_equivalentes_calculadas - COALESCE(h.Cantidad_Horas, 0)
                        WHEN h.Monto_Compensario > 0 
                        THEN h.Monto_Compensario / COALESCE(h.valor_hora_al_momento, 1000)
                        ELSE 0
                    END
                ), 0) as horas_completadas
            FROM plan_trabajos p
            LEFT JOIN horas_mensuales h ON (
                h.email = ? AND 
                h.mes = p.mes AND 
                h.anio = p.anio AND
                h.deleted_at IS NULL
            )
            WHERE p.user_id = ? AND p.deleted_at IS NULL
            GROUP BY p.id, p.mes, p.anio, p.horas_requeridas, p.created_at, p.updated_at
            ORDER BY p.anio DESC, p.mes DESC
        ", [$user->email, $user->id]);

        // Calcular porcentajes y formatear respuesta
        $resultado = array_map(function($plan) {
            $porcentaje = $plan->horas_requeridas > 0 ? 
                round(($plan->horas_completadas / $plan->horas_requeridas) * 100, 2) : 0;
            
            return [
                'id' => $plan->id,
                'mes' => $plan->mes,
                'anio' => $plan->anio,
                'horas_requeridas' => $plan->horas_requeridas,
                'horas_completadas' => round($plan->horas_completadas, 2),
                'horas_reales' => round($plan->horas_reales, 2),
                'horas_justificadas' => round($plan->horas_justificadas, 2),
                'progreso_porcentaje' => $porcentaje,
                'created_at' => $plan->created_at,
                'updated_at' => $plan->updated_at
            ];
        }, $planesConProgreso);

        return response()->json($resultado, 200);
    }

    // Método original sin cambios para compatibilidad
    public function progreso(Request $request, $id)
    {
        $userData = $request->user;
        if (is_array($userData)) {
            $userData = (object) $userData;
        }
        $user = \App\Models\User::where('email', $userData->email)->first();
        $plan = $user->planTrabajos()->findOrFail($id);
        
        // Obtener todas las horas mensuales del usuario para este plan
        $horasRegistradas = $user->horasMensuales()
            ->where('anio', $plan->anio)
            ->where('mes', $plan->mes)
            ->get();
        
        $horasReales = 0;
        $horasJustificadas = 0;
        
        foreach ($horasRegistradas as $hora) {
            // Sumar horas reales
            if ($hora->Cantidad_Horas) {
                $horasReales += $hora->Cantidad_Horas;
            }
            
            // Sumar horas justificadas (usar la conversión calculada cuando esté disponible)
            if ($hora->Monto_Compensario && $hora->Monto_Compensario > 0) {
                if ($hora->horas_equivalentes_calculadas) {
                    $horasJustificadas += $hora->horas_equivalentes_calculadas;
                } else {
                    // Fallback: calcular usando el valor histórico o 1000 como default
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
    }

    // Crear un nuevo plan de trabajo
    public function store(Request $request)
    {
        $userData = $request->user;
        if (is_array($userData)) {
            $userData = (object) $userData;
        }
        $user = \App\Models\User::where('email', $userData->email)->first();
        $plan = PlanTrabajo::create([
            'user_id' => $user->id,
            'mes' => $request->input('mes'),
            'anio' => $request->input('anio'),
            'horas_requeridas' => $request->input('horas_requeridas'),
        ]);
        return response()->json($plan, 201);
    }
}