<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Factura;
use Illuminate\Support\Facades\Storage;

class FacturasController extends Controller
{
    // Devuelve solo la URL pública del comprobante de una factura
    public function urlComprobante($id)
    {
        $factura = Factura::findOrFail($id);
        if (!$factura->Archivo_Comprobante) {
            return response()->json(['url' => null], 404);
        }
        $img = $factura->Archivo_Comprobante;
        if (!str_starts_with($img, 'http')) {
            $img = url('storage/' . ltrim($img, '/'));
        }
        return response()->json(['url' => $img], 200);
    }
    // Devuelve el detalle de una factura por ID
    public function Detalle(Request $request, $id)
    {
        $factura = Factura::findOrFail($id);
        // Devolver la URL pública de Archivo_Comprobante como imagen_comprobante
        if ($factura->Archivo_Comprobante) {
            $img = $factura->Archivo_Comprobante;
            if (!str_starts_with($img, 'http')) {
                $img = url('storage/' . ltrim($img, '/'));
            }
            $factura->imagen_comprobante = $img;
        } else {
            $factura->imagen_comprobante = null;
        }
        return response()->json(['factura' => $factura], 200);
    }
    // Filtro flexible por mes y año sobre created_at
    public function filtrarFacturas(Request $request)
    {
        $user = $request->user;
        if (!$user || !isset($user['email'])) {
            return response()->json(['error' => 'No se pudo obtener el email del usuario autenticado'], 401);
        }
        $email = $user['email'];
        $mes = $request->input('mes'); // Debe ser número (1-12) o null
        $anio = $request->input('anio'); // Puede ser null

        $query = Factura::where('email', $email);

        if (!empty($mes)) {
            $mesNum = intval($mes);
            if ($mesNum >= 1 && $mesNum <= 12) {
                $query->whereMonth('created_at', $mesNum);
            }
        }
        if (!empty($anio)) {
            $anioNum = intval($anio);
            if ($anioNum > 1900 && $anioNum < 2100) {
                $query->whereYear('created_at', '<=', $anioNum);
            }
        }

        $facturas = $query->orderBy('created_at', 'desc')->get();
        return response()->json(['facturas' => $facturas], 200);
    }
    // Filtra facturas por mes y año
    public function FiltrarPorMesAnio(Request $request)
    {
        $user = $request->user;
        if (!$user || !isset($user['email'])) {
            return response()->json(['error' => 'No se pudo obtener el email del usuario autenticado'], 401);
        }
        $email = $user['email'];
        $mesStr = $request->input('mes'); // string, por ejemplo 'Enero'
        $anio = $request->input('anio');
        if (!$mesStr || !$anio) {
            return response()->json(['error' => 'Debe enviar mes y año'], 400);
        }
        $meses = [
            'Enero'=>1,'Febrero'=>2,'Marzo'=>3,'Abril'=>4,'Mayo'=>5,'Junio'=>6,
            'Julio'=>7,'Agosto'=>8,'Septiembre'=>9,'Octubre'=>10,'Noviembre'=>11,'Diciembre'=>12
        ];
        $mes = $meses[$mesStr] ?? null;
        if (!$mes) {
            return response()->json(['error' => 'Mes inválido'], 400);
        }
        $facturas = Factura::where('email', $email)
            ->whereYear('created_at', $anio)
            ->whereMonth('created_at', $mes)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json(['facturas' => $facturas], 200);
    }
    // Cancela una factura por ID
    public function CancelarFactura(Request $request, $id)
    {
        $factura = Factura::findOrFail($id);
        $factura->delete();
        return response()->json([
            'mensaje' => 'Factura cancelada correctamente',
            'id' => $id
        ], 200);
    }
    // Elimina una factura por ID
    public function EliminarFactura(Request $request, $id)
    {
        $factura = Factura::findOrFail($id);
        $factura->delete();
        return response()->json([
            'mensaje' => 'Factura eliminada correctamente',
            'id' => $id
        ], 200);
    }
    // Lista todas las facturas del usuario autenticado
    public function ListarFacturasPorUsuario(Request $request)
    {
        $user = $request->user;
        if (!$user || !isset($user['email'])) {
            return response()->json(['error' => 'No se pudo obtener el email del usuario autenticado'], 401);
        }
        $email = $user['email'];
        $facturas = Factura::where('email', $email)->orderBy('created_at', 'desc')->get();
        return response()->json(['facturas' => $facturas], 200);
    }

    public function AgregarFactura(Request $request){
        $user = $request->user;
        if (!$user || !isset($user['email'])) {
            return response()->json(['error' => 'No se pudo obtener el email del usuario autenticado'], 401);
        }
        $monto = $request->input('Monto');
        if ($monto > 999999.99) {
            return response()->json(['error' => 'Un monto demasiado grande'], 422);
        }
        $factura = new Factura();
        $factura->email = $user['email'];
        $factura->Monto = $monto;
        // Manejar archivo comprobante
        if ($request->hasFile('Archivo_Comprobante')) {
            $path = $request->file('Archivo_Comprobante')->store('comprobantes', 'public');
            $factura->Archivo_Comprobante = $path;
        } else {
            $factura->Archivo_Comprobante = $request->input('Archivo_Comprobante', null);
        }
        // Estado_Pago por defecto o desde request
        $factura->Estado_Pago = $request->input('Estado_Pago', 'Pendiente');
        $factura->tipo_pago = $request->input('tipo_pago');

        // Asignar fecha_pago según Mes y Anio del request
        $mesStr = $request->input('Mes');
        $anio = $request->input('Anio');
        $meses = [
            'Enero'=>1,'Febrero'=>2,'Marzo'=>3,'Abril'=>4,'Mayo'=>5,'Junio'=>6,
            'Julio'=>7,'Agosto'=>8,'Septiembre'=>9,'Octubre'=>10,'Noviembre'=>11,'Diciembre'=>12
        ];
        $mes = $meses[$mesStr] ?? null;
        if ($mes && $anio) {
            // Primer día del mes seleccionado
            $factura->fecha_pago = \Carbon\Carbon::create($anio, $mes, 1)->toDateString();
        } else {
            $factura->fecha_pago = null;
        }

        $factura->save();
        return response()->json([
            "Factura agregada con exito" => true,
            "Factura" => $factura->id,
            "Archivo_Comprobante" => $factura->Archivo_Comprobante
        ], 201);
    }


}