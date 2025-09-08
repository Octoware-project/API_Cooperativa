<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Pago_Mensual;
use Illuminate\Support\Facades\Storage;

class FacturasController extends Controller
{
    public function AgregarPagoMensual(Request $request){
        $factura = new Pago_Mensual();
        $factura->email = $request->input('email');
        $factura->ID_Pago_Mensual = $request->input('ID_Pago_Mensual');
        $factura->Mes = $request->input('Mes');
        $factura->Monto = $request->input('Monto');
        $factura->Archivo_Comprobante = $request->input('Archivo_Comprobante');
        $factura->Fecha_Subida = $request->input('Fecha_Subida');
        $factura->Estado_Pago = $request->input('Estado_Pago');
        $factura->Comprobante_Inicial = $request->input('Comprobante_Inicial');
        $factura->tipo_pago = $request->input('tipo_pago');
        $factura->save();
        return response()->json([
            "Factura agregada con exito" => true,
            "ID_Pago_Mensual" => $factura->id,
            "Archivo_Comprobante" => $factura->Archivo_Comprobante
        ], 201);
    }

    public function EditarPagoMensual(Request $request, $id){
        $factura = Pago_Mensual::findOrFail($id);
        if ($request->has('Mes')) {
            $factura->Mes = $request->input('Mes');
        }
        if ($request->has('Monto')) {
            $factura->Monto = $request->input('Monto');
        }
        if ($request->has('Archivo_Comprobante')) {
            $factura->Archivo_Comprobante = $request->input('Archivo_Comprobante');
        }
        if ($request->has('Fecha_Subida')) {
            $factura->Fecha_Subida = $request->input('Fecha_Subida');
        }
        if ($request->has('Estado_Pago')) {
            $factura->Estado_Pago = $request->input('Estado_Pago');
        }
        if ($request->has('Comprobante_Inicial')) {
            $factura->Comprobante_Inicial = $request->input('Comprobante_Inicial');
        }
        if ($request->has('tipo_pago')) {
            $factura->tipo_pago = $request->input('tipo_pago');
        }
        $factura->save();
        return response()->json(['message' => 'Pago Editado Correctamente',
        'Pago_Mensual' => $factura], 201);
    }

    public function EliminarPagoMensual(Request $request, $id){
        $factura = Pago_Mensual::findOrFail($id);
        $factura->delete();
        return response()->json([
            "mensaje" => "Factura " . $id . " eliminada"
        ], 200);
    }

    public function Detalle(Request $request, $id){
        $factura = Pago_Mensual::findOrFail($id);
        return response()->json($factura, 201);
    }

    public function CalcularElTotalDeLasFacturas(Request $request, $id){
        $totalPagado = $factura = Pago_Mensual::findOrFail($id)->sum('Monto');
        return response()->json([
            'TotalPagado' => $totalPagado
        ], 200);
    }
}