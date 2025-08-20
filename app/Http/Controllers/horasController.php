<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\HorasRegistradas;
use App\Models\Conexion;

class horasController extends Controller
{
    public function CalcularHorasRegistradas (Request $request) {
        $id = $request->user()->id;

        $inicio = date('Y-m-01 00:00:00');
        $fin = date('Y-m-t 23:59:59');

        $total = HorasRegistradas::where('ID_Persona', $id)
            ->whereBetween('created_at', [$inicio, $fin])
            ->sum('Cantidad_Horas');

        return response()->json(['total_horas' => $total], 201);
    }

    public function AgregarHorasRegistradas(Request $request){
        $id = $request->user()->id;
        $horas = new HorasRegistradas();
        $horas->ID_Personas = $id;
        $horas->Semana = $request->input("Semana");
        $horas->Cantidad_Horas = $request->input("Cantidad_Horas");
        $horas->Monto_Compensario = $request->input("Monto_Compensario");

        $cantidadHoras = $request->input("Cantidad_Horas");
        if ($cantidadHoras == 0 || $cantidadHoras == null) {
            $horas->Motivo_Falla = $request->input("Motivo_Falla");
            $horas->Tipo_Justificacion = $request->input("Tipo_Justificacion");
        }
        $horas->save();
        return response()->json([
            "Horas ingresadas con exito" => true,
            "ID_Registro_Horas" => $horas->id
        ], 201);
    }

    public function EditarHorasRegistradas(Request $request){ 
        $id = $request->user()->id;
        $horas_id = $request->input("ID_Registro_Horas");

        $horas = HorasRegistradas::where('ID_Persona', $id)
            ->where('id', $horas_id)
            ->firstOrFail();

        $horas->Semana = $request->input("Semana");
        $horas->Cantidad_Horas = $request->input("Cantidad_Horas");
        $horas->Monto_Compensario = $request->input("Monto_Compensario");
        $horas->save();
        return response()->json(["Edición correcta" => true], 201);
    }

    public function EliminarHoras(Request $request){
        $id = $request->user()->id;
        $horas_id = $request->input("ID_Registro_Horas");

        $horas = HorasRegistradas::where('ID_Persona', $id)
            ->where('id', $horas_id)
            ->firstOrFail();

        $horas->delete();
        return response()->json([
            "Horas eliminadas" => true,
            "ID_Registro_Horas" => $horas_id
        ], 201);  
    }

    public function MostrarHorasRegistradas(Request $request){
        $id = $request->user()->id;
        $horas = HorasRegistradas::where('ID_Persona', $id)->get();
        return response()->json($horas);
    }

    public function BuscarParaEditar(Request $request){
        $id = $request->user()->id;
        $horas_id = $request->input("ID_Registro_Horas");

        if ($horas_id) {
        $horas = HorasRegistradas::where('ID_Persona', $id)
            ->where('id', $horas_id)
            ->firstOrFail();
        } else {
            $horas = HorasRegistradas::where('ID_Persona', $id)->get();
        }

        return response()->json($horas);
    } 
}
