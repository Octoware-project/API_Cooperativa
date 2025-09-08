<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Horas_Mensuales;

class Horas_MensualesController extends Controller
{
    public function Index(){
        $horasMensuales = Horas_Mensuales::all();
        return response()->json($horasMensuales, 200);

    }

    public function CalcularHorasRegistradas(Request $request) {
        $email = $request->input('email');
        $mes = $request->input('mes');
        if (!$email || !$mes) {
            return response()->json(['error' => 'Faltan parámetros email o mes'], 400);
        }
        $inicio = date('Y-m-01 00:00:00');
        $fin = date('Y-m-t 23:59:59');
        $total = Horas_Mensuales::where('email', $email)
            ->whereBetween('created_at', [$inicio, $fin])
            ->sum('Cantidad_Horas');
        return response()->json(['total_horas' => $total], 200);
    }

    public function AgregarHorasRegistradas(Request $request){
        $horasMensuales = new Horas_Mensuales();
        $horasMensuales->email = $request->input('email');
        $horasMensuales->Semana = $request->input('Semana');
        $horasMensuales->Cantidad_Horas = $request->input('Cantidad_Horas');
        $horasMensuales->Monto_Compensario = $request->input('Monto_Compensario');
        $cantidadHoras = $request->input('Cantidad_Horas');
        if ($cantidadHoras == 0 || $cantidadHoras == null) {
            $horasMensuales->Motivo_Falla = $request->input('Motivo_Falla');
            $horasMensuales->Tipo_Justificacion = $request->input('Tipo_Justificacion');
        }
        $horasMensuales->save();
        return response()->json($horasMensuales, 201);
    }

    public function EditarHorasRegistradas(Request $request, $id){
        $horasMensuales = Horas_Mensuales::findOrFail($id);
        if ($request->has('Semana')) {
            $horasMensuales->Semana = $request->input('Semana');
        }
        if ($request->has('Cantidad_Horas')) {
            $horasMensuales->Cantidad_Horas = $request->input('Cantidad_Horas');
        }
        if ($request->has('Monto_Compensario')) {
            $horasMensuales->Monto_Compensario = $request->input('Monto_Compensario');
        }
        $horasMensuales->save();
        return response()->json($horasMensuales, 200);
    }

    public function EliminarHoras(Request $request, $id){
        $horasMensuales = Horas_Mensuales::findOrFail($id);
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

