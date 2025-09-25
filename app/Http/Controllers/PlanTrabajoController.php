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
        // Sumar horas del usuario en el mes/año del plan
        $horasCumplidas = $user->horasMensuales()
            ->where('anio', $plan->anio)
            ->where('mes', $plan->mes)
            ->sum('Cantidad_Horas');
        $progreso = [
            'horas_requeridas' => $plan->horas_requeridas,
            'horas_cumplidas' => $horasCumplidas,
            'porcentaje' => $plan->horas_requeridas > 0 ? round(($horasCumplidas / $plan->horas_requeridas) * 100, 2) : 0
        ];
        return response()->json($progreso, 200);
    }
}
