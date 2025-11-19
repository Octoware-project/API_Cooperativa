<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Factura;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class FacturasController extends Controller
{
    private const MESES = [
        'Enero'=>1,'Febrero'=>2,'Marzo'=>3,'Abril'=>4,'Mayo'=>5,'Junio'=>6,
        'Julio'=>7,'Agosto'=>8,'Septiembre'=>9,'Octubre'=>10,'Noviembre'=>11,'Diciembre'=>12
    ];

    public function detalle(Request $request, $id)
    {
        try {
            $email = $request->user['email'];
            $factura = Factura::where('id', $id)
                             ->where('email', $email)
                             ->firstOrFail();
            
            if ($factura->Archivo_Comprobante) {
                $factura->imagen_comprobante = url("api/facturas/{$id}/comprobante");
            } else {
                $factura->imagen_comprobante = null;
            }
            
            return response()->json(['factura' => $factura], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Factura no encontrada o sin permisos'], 404);
        }
    }
    public function filtrarFacturas(Request $request)
    {
        try {
            $email = $request->user['email'];
            $mes = $request->input('mes');
            $anio = $request->input('anio');

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
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al filtrar facturas', 'mensaje' => $e->getMessage()], 500);
        }
    }
    public function filtrarPorMesAnio(Request $request)
    {
        try {
            $email = $request->user['email'];
            $mesStr = $request->input('mes');
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
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al filtrar facturas por mes y año', 'mensaje' => $e->getMessage()], 500);
        }
    }
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
   
    public function listarFacturasPorUsuario(Request $request)
    {
        try {
            $email = $request->user['email'];
            $facturas = Factura::where('email', $email)->orderBy('created_at', 'desc')->get();
            return response()->json(['facturas' => $facturas], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al listar facturas', 'mensaje' => $e->getMessage()], 500);
        }
    }

    public function agregarFactura(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'Monto' => 'required|numeric|min:0.01|max:999999.99',
                'tipo_pago' => 'required|string|max:50',
                'Mes' => 'required|string|in:' . implode(',', array_keys(self::MESES)),
                'Anio' => 'required|integer|min:2000|max:2100',
                'Estado_Pago' => 'nullable|string|in:Pendiente,Aceptado,Rechazado',
                'motivo' => 'required|string|min:5|max:500',
                'Archivo_Comprobante' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120' // Solo imágenes y PDF, 5MB max
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

            $factura = new Factura();
            $factura->email = $email;
            $factura->Monto = $monto;
            $factura->tipo_pago = $request->input('tipo_pago');
            $factura->Estado_Pago = $request->input('Estado_Pago', 'Pendiente');
            $factura->motivo = $request->input('motivo');

            if ($request->hasFile('Archivo_Comprobante')) {
                $file = $request->file('Archivo_Comprobante');
                
                // Validar que el archivo sea válido
                if ($file->isValid()) {
                    // Generar nombre único con extensión original
                    $extension = $file->getClientOriginalExtension();
                    $filename = uniqid('comprobante_') . '.' . $extension;
                    
                    // Guardar el archivo
                    $path = $file->storeAs('comprobantes', $filename, 'public');
                    $factura->Archivo_Comprobante = $path;
                    
                    // Log para debugging
                    Log::info('Archivo guardado exitosamente', [
                        'path' => $path,
                        'size' => $file->getSize(),
                        'mime' => $file->getMimeType(),
                        'original_name' => $file->getClientOriginalName()
                    ]);
                } else {
                    Log::error('Archivo inválido recibido');
                    return response()->json([
                        'error' => 'El archivo recibido no es válido'
                    ], 400);
                }
            } else {
                $factura->Archivo_Comprobante = null;
                Log::warning('No se recibió archivo de comprobante');
            }

            $mes = self::MESES[$mesStr];
            $factura->fecha_pago = \Carbon\Carbon::create($anio, $mes, 1)->toDateString();

            // Establecer created_at con el mes y año seleccionados
            $fechaCreacion = \Carbon\Carbon::create($anio, $mes, 1);
            $factura->created_at = $fechaCreacion;
            $factura->updated_at = $fechaCreacion;

            // Desactivar timestamps temporalmente para que Laravel no los sobrescriba
            $factura->timestamps = false;
            $factura->save();
            $factura->timestamps = true;

            return response()->json([
                'success' => true,
                'mensaje' => 'Factura agregada con éxito',
                'factura' => [
                    'id' => $factura->id,
                    'monto' => $factura->Monto,
                    'tipo_pago' => $factura->tipo_pago,
                    'estado_pago' => $factura->Estado_Pago,
                    'fecha_pago' => $factura->fecha_pago,
                    'motivo' => $factura->motivo,
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

    public function urlComprobante(Request $request, $id)
    {
        try {
            $email = $request->user['email'];
            $factura = Factura::where('id', $id)
                             ->where('email', $email)
                             ->firstOrFail();
            
            if (!$factura->Archivo_Comprobante) {
                return response()->json(['error' => 'No hay comprobante disponible'], 404);
            }

            $url = url('storage/' . ltrim($factura->Archivo_Comprobante, '/'));
            
            return response()->json([
                'url' => $url,
                'filename' => basename($factura->Archivo_Comprobante),
                'tipo' => pathinfo($factura->Archivo_Comprobante, PATHINFO_EXTENSION)
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Factura no encontrada o sin permisos'], 404);
        }
    }

    public function servirComprobante(Request $request, $id)
    {
        try {
            // Obtener email del usuario autenticado
            $email = $request->user['email'] ?? null;
            
            if (!$email) {
                abort(403, 'No autorizado');
            }
            
            $factura = Factura::where('id', $id)
                             ->where('email', $email)
                             ->firstOrFail();
            
            if (!$factura->Archivo_Comprobante) {
                abort(404, 'Comprobante no encontrado');
            }

            $filePath = storage_path('app/public/' . $factura->Archivo_Comprobante);
            
            if (!file_exists($filePath)) {
                abort(404, 'Archivo no encontrado');
            }

            // Determinar tipo MIME correcto basado en la extensión
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'pdf' => 'application/pdf',
                'webp' => 'image/webp'
            ];
            
            $mimeType = $mimeTypes[$extension] ?? mime_content_type($filePath);
            $fileName = basename($factura->Archivo_Comprobante);

            $isDownload = $request->query('download', false);
            $disposition = $isDownload ? 'attachment' : 'inline';
            
            // Limpiar cualquier salida previa que pueda corromper el archivo
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Leer archivo binario
            $fileContent = file_get_contents($filePath);
            
            return response($fileContent, 200)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', $disposition . '; filename="' . $fileName . '"')
                ->header('Content-Length', strlen($fileContent))
                ->header('Cache-Control', 'public, max-age=3600')
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Authorization, Content-Type, Accept')
                ->header('Access-Control-Expose-Headers', 'Content-Type, Content-Disposition, Content-Length')
                ->header('Accept-Ranges', 'bytes');

        } catch (\Exception $e) {
            abort(404, 'Comprobante no encontrado');
        }
    }

    public function descargarComprobante($id)
    {
        try {
            $factura = Factura::findOrFail($id);
            
            if (!$factura->Archivo_Comprobante) {
                abort(404, 'Comprobante no encontrado');
            }

            $filePath = storage_path('app/public/' . $factura->Archivo_Comprobante);
            
            if (!file_exists($filePath)) {
                abort(404, 'Archivo no encontrado');
            }

            return response()->file($filePath);

        } catch (\Exception $e) {
            abort(404, 'Comprobante no encontrado');
        }
    }


}