<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlanTrabajo;

class PlanTrabajoController extends Controller
{
    // Listar planes de trabajo del usuario autenticado
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

    // Progreso de un plan de trabajo
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
}
