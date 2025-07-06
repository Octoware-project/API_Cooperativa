<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\HorasRegistradas;

class horasController extends Controller
{
    public function Index(){
        $horas = HorasRegistradas::all();
        return view("index", ["bebidas" => $horas]);
    }

    public function CalcularHorasRegistradas (Request $request) {
        $id = $request->post("ID_Persona");
        $inicio = date('Y-m-01 00:00:00');
        $fin = date('Y-m-t 23:59:59');

        $total = HorasRegistradas::where('ID_Persona', $id)
            ->whereBetween('created_at', [$inicio, $fin])
            ->sum('Cantidad_Horas');

        return response()->json(['total_horas' => $total]);
    }

    public function AgregarHorasRegistradas(Request $request){
        $horas = new HorasRegistradas();
        $horas->ID_Persona = $request->post("ID_Persona");
        $horas->Semana = $request->post("Semana");
        $horas->Cantidad_Horas = $request->post("Cantidad_Horas");
        $horas->Monto_Compensario = $request->post("Monto_Compensario");

        $cantidadHoras = $request->post("Cantidad_Horas");
        if ($cantidadHoras == 0 || $cantidadHoras == null) {
            $horas->Motivo_Falla = $request->post("Motivo_Falla");
            $horas->Tipo_Justificacion = $request->post("Tipo_Justificacion");
        }
        $horas->save();
        return response()->json(["Factura agregada con exito" => true]);
    }

    public function EditarHorasRegistradas(Request $request){ 
        $horas = HorasRegistradas::findOrFail($request->id);
        $horas->ID_Persona = $request->post("ID_Persona");
        $horas->Semana = $request->post("Semana");
        $horas->Cantidad_Horas = $request->post("Cantidad_Horas");
        $horas->Monto_Compensario = $request->post("Monto_Compensario");
        $horas->save();
        eturn response()->json(["Edición correcta" => true]);
    }

    public function EliminarHoras(Request $request, $id){
        $horas = HorasRegistradas::findOrFail($id);
        $horas->delete();
        return redirect("/")->with("Horas Registradas Eliminadas", true);
    }

    public function MostrarHorasRegistradas(Request $request, $id){
        $horas = HorasRegistradas::findOrFail($id);
        return response()->json($horas);
    }

    public function BuscarParaEditar(Request $request, $id){
        $horas = HorasRegistradas::findOrFail($id);
        return view("editar", ["Horas" => $horas]);
    }


}
