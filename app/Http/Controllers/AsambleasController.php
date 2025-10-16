<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JuntaAsamblea;
use Illuminate\Support\Facades\Validator;

class AsambleasController extends Controller
{
    /**
     * Obtener todas las asambleas ordenadas por fecha descendente
     */
    public function index(Request $request)
    {
        try {
            // Obtener todas las asambleas ordenadas por fecha (más reciente primero)
            $asambleas = JuntaAsamblea::orderByFechaDesc()->get();

            // Formatear las fechas para mejor presentación
            $asambleasFormateadas = $asambleas->map(function ($asamblea) {
                return [
                    'id' => $asamblea->id,
                    'lugar' => $asamblea->lugar,
                    'fecha' => $asamblea->fecha->format('d/m/Y'),
                    'fecha_raw' => $asamblea->fecha->format('Y-m-d'),
                    'detalle' => $asamblea->detalle,
                    'es_futura' => $asamblea->fecha >= now()->format('Y-m-d'),
                    'created_at' => $asamblea->created_at,
                    'updated_at' => $asamblea->updated_at
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Asambleas obtenidas correctamente',
                'data' => $asambleasFormateadas,
                'total' => $asambleasFormateadas->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las asambleas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalles de una asamblea específica
     */
    public function show(Request $request, $id)
    {
        try {
            $asamblea = JuntaAsamblea::findOrFail($id);

            $asambleaFormateada = [
                'id' => $asamblea->id,
                'lugar' => $asamblea->lugar,
                'fecha' => $asamblea->fecha->format('d/m/Y'),
                'fecha_raw' => $asamblea->fecha->format('Y-m-d'),
                'detalle' => $asamblea->detalle,
                'es_futura' => $asamblea->fecha >= now()->format('Y-m-d'),
                'created_at' => $asamblea->created_at,
                'updated_at' => $asamblea->updated_at
            ];

            return response()->json([
                'success' => true,
                'message' => 'Asamblea obtenida correctamente',
                'data' => $asambleaFormateada
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Asamblea no encontrada',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Obtener solo las asambleas futuras
     */
    public function futuras(Request $request)
    {
        try {
            $asambleas = JuntaAsamblea::futuras()->orderByFechaDesc()->get();

            $asambleasFormateadas = $asambleas->map(function ($asamblea) {
                return [
                    'id' => $asamblea->id,
                    'lugar' => $asamblea->lugar,
                    'fecha' => $asamblea->fecha->format('d/m/Y'),
                    'fecha_raw' => $asamblea->fecha->format('Y-m-d'),
                    'detalle' => $asamblea->detalle,
                    'es_futura' => true,
                    'created_at' => $asamblea->created_at,
                    'updated_at' => $asamblea->updated_at
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Asambleas futuras obtenidas correctamente',
                'data' => $asambleasFormateadas,
                'total' => $asambleasFormateadas->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las asambleas futuras',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener solo las asambleas pasadas
     */
    public function pasadas(Request $request)
    {
        try {
            $asambleas = JuntaAsamblea::pasadas()->orderByFechaDesc()->get();

            $asambleasFormateadas = $asambleas->map(function ($asamblea) {
                return [
                    'id' => $asamblea->id,
                    'lugar' => $asamblea->lugar,
                    'fecha' => $asamblea->fecha->format('d/m/Y'),
                    'fecha_raw' => $asamblea->fecha->format('Y-m-d'),
                    'detalle' => $asamblea->detalle,
                    'es_futura' => false,
                    'created_at' => $asamblea->created_at,
                    'updated_at' => $asamblea->updated_at
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Asambleas pasadas obtenidas correctamente',
                'data' => $asambleasFormateadas,
                'total' => $asambleasFormateadas->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las asambleas pasadas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}