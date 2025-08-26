<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Horas_Mensuales;

class Horas_MensualesController extends Controller
{
    public function Index(){
        $horasMensuales = Horas_Mensuales::all();
        return response()->json($horasMensuales, 201);

    }

    public function CalcularHorasRegistradas (Request $request) {
        $inicio = date('Y-m-01 00:00:00');
        $fin = date('Y-m-t 23:59:59');

        $total = Horas_Mensuales::where('ID_Persona', $id)
            ->whereBetween('created_at', [$inicio, $fin])
            ->sum('Cantidad_Horas');

        return response()->json(['total_horas' => $total], 201);
    }

    public function AgregarHorasRegistradas(Request $request){
        $horasMensuales = new Horas_Mensuales();
        $horasMensuales->usuario = $request->user["email"];
        $horasMensuales->semana = $request->post("Semana");
        $horasMensuales->cantidadHoras = $request->post("Cantidad_Horas");
        $horasMensuales->montoCompensatorio = $request->post("Monto_Compensatorio");

        $cantidadHoras = $request->post("Cantidad_Horas");;
        if ($cantidadHoras == 0 || $cantidadHoras == null) {
            $horasMensuales->motivoFalla = $request->post("Motivo_Falla");
            $horasMensuales->justificacion = $request->post("Tipo_Justificacion");
        }
        $horasMensuales->save();
        return response()->json($horasMensuales, 201);
    }

    public function EditarHorasRegistradas(Request $request){ 
        $horasMensuales = Horas_Mensuales::findOrFail($id);
        $horasMensuales->semana = $request->post("Semana");
        $horasMensuales->cantidadHoras = $request->post("Cantidad_Horas");
        $horasMensuales->montoCompensatorio = $request->post("Monto_Compensatorio");
        $horasMensuales->save();
        return response()->json($horasMensuales, 201);
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
        return response()->json($horasMensuales, 201);
    }

}
