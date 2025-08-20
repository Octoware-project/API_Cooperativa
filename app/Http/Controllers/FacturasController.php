<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Pago_Mensual;

class FacturasController extends Controller
{
    public function AgregarPagoMensual(Request $request){
        $id = $request->user()->id;
       
        $factura = new Pago_Mensual();
        $factura->ID_Persona = $id;
        $factura->Mes = $request->input("Mes");
        $factura->Monto = $request->input("Monto");
        
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
        $id = $request->user()->id;
        $factura_id = $request->input("ID_Pago_Mensual");

        $factura = Pago_Mensual::where('ID_Persona', $id)
            ->where('id', $factura_id)
            ->firstOrFail();

        $factura->Mes = $request->input("Mes");
        $factura->Monto = $request->input("Monto");
        
        if ($request->hasFile('Archivo_Comprobante')) {
            $archivo = $request->file('Archivo_Comprobante');
            $contenido = file_get_contents($archivo->getRealPath());
            $factura->Archivo_Comprobante = $contenido;
        }

        $factura->Fecha_Subida = $request->input("Fecha_Subida");

        $factura->save();
        return response()->json(['message' => 'Pago Editado Correctamente',
        'Pago_Mensual' => $factura], 201);
    }

    public function EliminarPagoMensual(Request $request){
        $id = $request->user()->id;
        $factura_id = $request->input("ID_Pago_Mensual");

        $factura = Pago_Mensual::where('ID_Persona', $id)
            ->where('id', $factura_id)
            ->firstOrFail();

        $factura->delete();
        return response()->json([
            "Horas eliminadas" => true,
            "ID_Pago_Mensual" => $factura_id
        ], 201); 
    }

    public function MostrarPagoMensual(Request $request){
        $id = $request->user()->id;
        $factura = Pago_Mensual::where('ID_Persona', $id)->get();
        return response()->json($factura);
    }

    public function BuscarParaEditar(Request $request){
        $id = $request->user()->id;
        $factura_id = $request->input("ID_Pago_Mensual");

        if ($factura_id) {
        $factura = Pago_Mensual::where('ID_Persona', $id)
            ->where('id', $factura_id)
            ->firstOrFail();
        } else {
            $factura = Pago_Mensual::where('ID_Persona', $id)->get();
        }

        return response()->json($factura);
    }

    public function CalcularElTotalDeLasFacturas(Request $request){
        $id = $request->user()->id;

        $totalPagado = Pago_Mensual::where('ID_Persona', $id)->sum('Monto');

        return response()->json([
            'TotalPagado' => $totalPagado
        ], 200);
    }
}
