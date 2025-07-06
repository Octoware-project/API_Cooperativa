<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Pago_Mensual;

class FacturasController extends Controller
{
    public function Index(){
        $factura = Pago_Mensual::all();
        return view("index", ["bebidas" => $factura]);
    }

    public function AgregarPagoMensual(Request $request){
        $factura = new Pago_Mensual();
        $factura->ID_Persona = $request->input("ID_Persona");
        $factura->Monto = $request->input("Monto");
        

        if ($request->hasFile('Archivo_Comprobante')) {
            $archivo = $request->file('Archivo_Comprobante');
            $contenido = file_get_contents($archivo->getRealPath());
            $factura->Archivo_Comprobante = $contenido;
        } else {
            return back()->withErrors(['Archivo_Comprobante' => 'Debe subir un archivo.']);
        }
        
        $factura->Fecha_Subida = $request->input("Fecha_Subida");

        $factura->save();
        return redirect("/")->with("Todo Correcto", true);
    }

    public function EditarPagoMensual(Request $request){
        $factura = Pago_Mensual::findOrFail($request->id);
        $factura->ID_Persona = $request->input("ID_Persona");
        $factura->Monto = $request->input("Monto");
        
        if ($request->hasFile('Archivo_Comprobante')) {
            $archivo = $request->file('Archivo_Comprobante');
            $contenido = file_get_contents($archivo->getRealPath());
            $factura->Archivo_Comprobante = $contenido;
        }

        $factura->Fecha_Subida = $request->input("Fecha_Subida");

        $factura->save();
        return redirect("/")->with("Pago mensual correctamente editado", true);
    }

    public function EliminarPagoMensual(Request $request, $id){
        $factura = Pago_Mensual::findOrFail($id);
        $factura->delete();
        return redirect("/")->with("Pago Eliminado", true);
    }

    public function MostrarPagoMensual(Request $request, $id){
        $factura = Pago_Mensual::findOrFail($id); 
        return view("mostrarDetalles", ["PagoMensual" => $factura]);
    }

    public function BuscarParaEditar(Request $request, $id){
        $factura = Pago_Mensual::findOrFail($id);
        return view("editar", ["Pago Mensual" => $factura]);
    }

    public function CalcularElTotalDeLasFacturas(Request $request){
        $idPersona = $request->input('ID_Persona'); // o ->get('ID_Persona')
        $totalPagado = Pago_Mensual::where('ID_Persona', $idPersona)->sum('Monto');
        return view('saldo_total', ['total' => $totalPagado]);

        if (!$idPersona) {
        return back()->withErrors(['ID_Persona' => 'Debe ingresar un ID de persona.']);
        }
    }

}