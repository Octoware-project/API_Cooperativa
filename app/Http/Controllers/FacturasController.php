<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Facturas;

class FacturasController extends Controller
{
    public function Index(){
        $factua = Pago_Mensual::all();
        return view("index", ["bebidas" => $factura]);
    }

    $archivo = $request->post("Archivo_Comprobante");

    public function AgregarPagoMensual(Request $request){
        $factura = new Pago_Mensual();
        $factura->ID_Persona = $request->post("ID_Persona");
        $factura->Monto = $request->post("Monto");
        

        if ($request->hasFile('Archivo_Comprobante')) {
            $archivo = $request->file('Archivo_Comprobante');
            $contenido = file_get_contents($archivo->getRealPath());
            $factura->Archivo_Comprobante = $contenido;
        } else {
            return back()->withErrors(['Archivo_Comprobante' => 'Debe subir un archivo.']);
        }
        
        $factura->Fecha_Subida = $request->post("Fecha_Subida");

        $factura->save();
        return redirect("/")->with("Todo Correcto", true);
    }

    public function EditarPagoMensual(Request $request){
        $factura = Pago_Mensual::findOrFail($request->id);
        $factura->ID_Persona = $request->post("ID_Persona");
        $factura->Monto = $request->post("Monto");
        $factura->Archivo_Comprobante = $request->post("Mes");
        
        if ($request->hasFile('Archivo_Comprobante')) {
            $archivo = $request->file('Archivo_Comprobante');
            $contenido = file_get_contents($archivo->getRealPath());
            $factura->Archivo_Comprobante = $contenido;
        }

        $factura->Fecha_Subida = $request->post("Fecha_Subida");

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
        return view("editar", ["Pago Mensual" => $horas]);
    }


}