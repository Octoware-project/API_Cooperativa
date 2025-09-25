<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Factura;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FacturasController extends Controller
{
    // Array constante para los meses
    private const MESES = [
        'Enero'=>1,'Febrero'=>2,'Marzo'=>3,'Abril'=>4,'Mayo'=>5,'Junio'=>6,
        'Julio'=>7,'Agosto'=>8,'Septiembre'=>9,'Octubre'=>10,'Noviembre'=>11,'Diciembre'=>12
    ];

    // Devuelve el detalle de una factura por ID
    public function detalle(Request $request, $id)
    {
        try {
            $email = $request->user['email'];
            $factura = Factura::where('id', $id)
                             ->where('email', $email)
                             ->firstOrFail();
            
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
        } catch (\Exception $e) {
            return response()->json(['error' => 'Factura no encontrada o sin permisos'], 404);
        }
    }
    // Filtro flexible por mes y año sobre created_at
    public function filtrarFacturas(Request $request)
    {
        $email = $request->user['email'];
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
                $query->whereYear('created_at', $anioNum);
            }
        }

        $facturas = $query->orderBy('created_at', 'desc')->get();
        return response()->json(['facturas' => $facturas], 200);
    }
    // Filtra facturas por mes y año
    public function filtrarPorMesAnio(Request $request)
    {
        $email = $request->user['email'];
        $mesStr = $request->input('mes'); // string, por ejemplo 'Enero'
        $anio = $request->input('anio');
        
        if (!$mesStr || !$anio) {
            return response()->json(['error' => 'Debe enviar mes y año'], 400);
        }
        
        $mes = self::MESES[$mesStr] ?? null;
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
    public function cancelarFactura(Request $request, $id)
    {
        try {
            $email = $request->user['email'];
            $factura = Factura::where('id', $id)
                             ->where('email', $email)
                             ->firstOrFail();
            
            $factura->delete();
            
            return response()->json([
                'mensaje' => 'Factura cancelada correctamente',
                'id' => $id
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Factura no encontrada o sin permisos'], 404);
        }
    }
   
    // Lista todas las facturas del usuario autenticado
    public function listarFacturasPorUsuario(Request $request)
    {
        $email = $request->user['email'];
        $facturas = Factura::where('email', $email)->orderBy('created_at', 'desc')->get();
        return response()->json(['facturas' => $facturas], 200);
    }

    public function agregarFactura(Request $request)
    {
        try {
            // Validación de datos de entrada
            $validator = Validator::make($request->all(), [
                'Monto' => 'required|numeric|min:0.01|max:999999.99',
                'tipo_pago' => 'required|string|max:50',
                'Mes' => 'required|string|in:' . implode(',', array_keys(self::MESES)),
                'Anio' => 'required|integer|min:2000|max:2100',
                'Estado_Pago' => 'nullable|string|in:Pendiente,Pagado,Cancelado',
                'Archivo_Comprobante' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120' // 5MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Datos de validación incorrectos',
                    'errores' => $validator->errors()
                ], 422);
            }

            $email = $request->user['email'];
            $monto = $request->input('Monto');
            $mesStr = $request->input('Mes');
            $anio = $request->input('Anio');

            // Crear nueva factura
            $factura = new Factura();
            $factura->email = $email;
            $factura->Monto = $monto;
            $factura->tipo_pago = $request->input('tipo_pago');
            $factura->Estado_Pago = $request->input('Estado_Pago', 'Pendiente');

            // Manejar archivo comprobante
            if ($request->hasFile('Archivo_Comprobante')) {
                $path = $request->file('Archivo_Comprobante')->store('comprobantes', 'public');
                $factura->Archivo_Comprobante = $path;
            } else {
                $factura->Archivo_Comprobante = null;
            }

            // Asignar fecha_pago según Mes y Anio del request
            $mes = self::MESES[$mesStr];
            $factura->fecha_pago = \Carbon\Carbon::create($anio, $mes, 1)->toDateString();

            $factura->save();

            return response()->json([
                'success' => true,
                'mensaje' => 'Factura agregada con éxito',
                'factura' => [
                    'id' => $factura->id,
                    'monto' => $factura->Monto,
                    'tipo_pago' => $factura->tipo_pago,
                    'estado_pago' => $factura->Estado_Pago,
                    'fecha_pago' => $factura->fecha_pago,
                    'archivo_comprobante' => $factura->Archivo_Comprobante
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error interno del servidor',
                'mensaje' => 'No se pudo crear la factura'
            ], 500);
        }
    }


}