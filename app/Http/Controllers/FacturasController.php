<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Pago_Mensual;

class FacturasController extends Controller
{
    public function AgregarPagoMensual(Request $request){
        $factura = new Pago_Mensual();
        $factura->usuario = $request->user["email"];
        $factura->Mes = $request->post("Mes");
        $factura->Monto = $request->post("Monto");
        
        if ($request->hasFile('Archivo_Comprobante')) {
            $archivo = $request->file('Archivo_Comprobante');
            $contenido = file_get_contents($archivo->getRealPath());
            $factura->Archivo_Comprobante = $contenido;
        } else {
            return response()->json(['message' => 'Debe subir un archivo comprobante'], 500);
        }
        
        $factura->Fecha_Subida = $request->input("Fecha_Subida");

        $factura->save();
        return response()->json([
            "Factura agregada con exito" => true,
            "ID_Pago_Mensual" => $factura->id
        ], 201);
    }

    public function EditarPagoMensual(Request $request){
        $factura = Pago_Mensual::findOrFail($id);
        $factura_id = $request->post("ID_Pago_Mensual");
        $factura->Mes = $request->post("Mes");
        $factura->Monto = $request->post("Monto");
        
        if ($request->hasFile('Archivo_Comprobante')) {
            $archivo = $request->file('Archivo_Comprobante');
            $contenido = file_get_contents($archivo->getRealPath());
            $factura->Archivo_Comprobante = $contenido;
        }
        $factura->Fecha_Subida = $request->post("Fecha_Subida");
        $factura->save();
        return response()->json(['message' => 'Pago Editado Correctamente',
        'Pago_Mensual' => $factura], 201);
    }

    public function EliminarPagoMensual(Request $request, $id){
        $factura = Pago_Mensual::findOrFail($id);
        $factura->delete();
        return [
            "mensaje" => "Factura " . $id . " eliminada"
        ];
    }

    public function Detalle(Request $request, $id){
        $factura = Pago_Mensual::findOrFail($id);
        return response()->json($horasMensuales, 201);
    }

    public function CalcularElTotalDeLasFacturas(Request $request, $id){
        $totalPagado = $factura = Pago_Mensual::findOrFail($id)->sum('Monto');
        return response()->json([
            'TotalPagado' => $totalPagado
        ], 200);
    }
}