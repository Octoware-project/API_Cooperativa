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

    public function CalcularHorasRegistradas () {
        $inicio = date('Y-m-01 00:00:00');
        $fin = date('Y-m-t 23:59:59');

        $total = HorasRegistradas::where('ID_Persona')
        ->whereBetween('created_at',[$inicio, $fin])
        ->suma('Cantidad_Horas');
    }

    $cantidadHoras = $request->post("Cantidad_Horas");

    public function AgregarHorasRegistradas(Request $request){
        $horas = new HorasRegistradas();
        $horas->ID_Persona = $request->post("ID_Persona");
        $horas->Semana = $request->post("Semana");
        $horas->Cantidad_Horas = $request->post("Cantidad_Horas");
        $horas->Monto_Compensario = $request->post("Monto_Compensario");
        
        if ($cantidadHoras == 0 || $cantidadHoras == null) {
            $horas->Motivo_Falla = $request->post("Motivo_Falla");
            $horas->Tipo_Justificacion = $request->post("Tipo_Justificacion");
        }
        $horas->save();
        return redirect("/")->with("Todo Correcto", true);
    }

    public function EditarHorasRegistradas(Request $request){
        $horas = HorasRegistradas::findOrFail($request->id);
        $horas->ID_Persona = $request->post("ID_Persona");
        $horas->Semana = $request->post("Semana");
        $horas->Cantidad_Horas = $request->post("Cantidad_Horas");
        $horas->Monto_Compensario = $request->post("Monto_Compensario");
        $horas->save();
        return redirect("/")->with("Horas Registradas editadas", true);
    }

    public function EliminarHoras(Request $request, $id){
        $horas = HorasRegistradas::findOrFail($id);
        $horas->delete();
        return redirect("/")->with("Horas Registradas Eliminadas", true);
    }

    public function MostrarHorasRegistradas(Request $request, $id){
        $horas = HorasRegistradas::findOrFail($id); 
        return view("mostrarDetalles", ["HorasRegistradas" => $horas]);
    }

    public function BuscarParaEditar(Request $request, $id){
        $horas = HorasRegistradas::findOrFail($id);
        return view("editar", ["Horas" => $horas]);
    }


}