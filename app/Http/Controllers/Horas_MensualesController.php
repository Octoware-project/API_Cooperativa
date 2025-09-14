<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Horas_Mensuales;

class Horas_MensualesController extends Controller
{
    // Lista todos los registros de horas del usuario autenticado
    public function listarHorasPorUsuario(Request $request)
    {
        $user = $request->user;
        if (is_array($user)) {
            $user = (object) $user;
        }
        if (!$user || !isset($user->email)) {
            return response()->json(['error' => 'No se pudo obtener el email del usuario autenticado'], 401);
        }
        $email = $user->email;
        $horas = \App\Models\Horas_Mensuales::where('email', $email)->get();
        return response()->json(['horas' => $horas], 200);
    }
    
    // Suma todas las Cantidad_Horas del último mes para un email
    public function sumarHorasUltimoMes(Request $request)
    {

        $user = $request->user;
        if (is_array($user)) {
            $user = (object) $user;
        }
        if (!$user || !isset($user->email)) {
            return response()->json(['error' => 'No se pudo obtener el email del usuario autenticado'], 401);
        }
        $email = $user->email;
        $now = now();
        $inicio = $now->copy()->startOfMonth();
        $fin = $now->copy()->endOfMonth();
        $total = \App\Models\Horas_Mensuales::where('email', $email)
            ->whereBetween('created_at', [$inicio, $fin])
            ->sum('Cantidad_Horas');
        return response()->json(['total_horas_ultimo_mes' => $total], 200);
    }
    public function Index(){
        $user = request()->user;
        if (is_array($user)) {
            $user = (object) $user;
        }
        if (!$user || !isset($user->email)) {
            return response()->json(['error' => 'No se pudo obtener el email del usuario autenticado'], 401);
        }
        $horasMensuales = Horas_Mensuales::where('email', $user->email)->get();
        return response()->json($horasMensuales, 200);

    }

    public function CalcularHorasRegistradas(Request $request) {
        $user = $request->user;
        if (is_array($user)) {
            $user = (object) $user;
        }
        if (!$user || !isset($user->email)) {
            return response()->json(['error' => 'No se pudo obtener el email del usuario autenticado'], 401);
        }
        $email = $user->email;
        $mes = $request->input('mes');
        $anio = $request->input('anio', now()->year);
        if (!$mes) {
            return response()->json(['error' => 'Falta parámetro mes'], 400);
        }
        $total = Horas_Mensuales::where('email', $email)
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->sum('Cantidad_Horas');
        return response()->json(['total_horas' => $total], 200);
    }

    public function agregarHoras(Request $request){
        $user = $request->user;
        if (is_array($user)) {
            $user = (object) $user;
        }
        if (!$user || !isset($user->email)) {
            return response()->json(['error' => 'No se pudo obtener el email del usuario autenticado'], 401);
        }
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
        return response()->json($horasMensuales, 201);
    }

    public function AgregarJustificacion(Request $request){
        $user = $request->user;
        if (is_array($user)) {
            $user = (object) $user;
        }
        if (!$user || !isset($user->email)) {
            return response()->json(['error' => 'No se pudo obtener el email del usuario autenticado'], 401);
        }
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
        return response()->json($horasMensuales, 201);
    }

    public function EditarHorasRegistradas(Request $request, $id){
        $horasMensuales = Horas_Mensuales::findOrFail($id);
        if ($request->has('dia')) {
            $horasMensuales->dia = $request->input('dia');
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
        return response()->json($horasMensuales, 200);
    }

    public function EliminarHoras(Request $request, $id){
        $user = $request->user;
        if (is_array($user)) {
            $user = (object) $user;
        }
        if (!$user || !isset($user->email)) {
            return response()->json(['error' => 'No se pudo obtener el email del usuario autenticado'], 401);
        }
        $horasMensuales = Horas_Mensuales::where('id', $id)->where('email', $user->email)->firstOrFail();
        // Validar antigüedad menor a 24 horas
        if ($horasMensuales->created_at->diffInHours(now()) >= 24) {
            return response()->json(['error' => 'Solo se pueden cancelar registros con menos de 24 horas de creados'], 403);
        }
        $horasMensuales->delete();
        return [
            "mensaje" => "Horas " . $id . " eliminadas"
        ];  
    }

    public function Detalle(Request $request, $id){
        $horasMensuales = Horas_Mensuales::findOrFail($id);
        return response()->json($horasMensuales, 200);
    }

}

