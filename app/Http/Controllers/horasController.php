<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\horasRegistradas;

class horasController extends Controller
{
    public function AgregarHorasRegistradas(Request $request){
        $horas = new HorasRegistradas();
        $horas->ID_Persona = $request->post("ID_Persona");
        $horas->Semana = $request->post("Semana");
        $horas->Cantidad_Horas = $request->post("Cantidad_Horas");
        $horas->Monto_Compensario = $request->post("Monto_Compensario");
        $horas->save();
        return redirect("/")->with("Horas Registradas agregadas", true);
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