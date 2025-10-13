<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlanTrabajo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PlanTrabajoController extends Controller
{
    // Listar planes de trabajo del usuario autenticado - OPTIMIZADO
    public function index(Request $request)
    {
        try {
            $userData = $request->user;
            if (is_array($userData)) {
                $userData = (object) $userData;
            }
            
            $userEmail = $userData->email;
            
            // Query directa ultra-rápida
            $planes = DB::select("
                SELECT p.id, p.mes, p.anio, p.horas_requeridas, p.created_at, p.updated_at
                FROM plan_trabajos p
                INNER JOIN users u ON u.id = p.user_id AND u.email = ?
                WHERE p.deleted_at IS NULL
                ORDER BY p.anio DESC, p.mes DESC
                LIMIT 50
            ", [$userEmail]);
            
            return response()->json($planes, 200);
            
        } catch (\Exception $e) {
            \Log::error('Error en planes index: ' . $e->getMessage());
            return response()->json([], 200);
        }
    }

    // ENDPOINT SIMPLE SIN PROGRESO - Para resolver timeout
    public function indexWithProgress(Request $request)
    {
        try {
            $userData = $request->user;
            if (is_array($userData)) {
                $userData = (object) $userData;
            }
            
            $userEmail = $userData->email;
            
            // Obtener solo los planes básicos primero
            $userId = DB::table('users')->where('email', $userEmail)->value('id');
            
            if (!$userId) {
                return response()->json([], 200);
            }
            
            // Query súper simple - solo planes, sin JOIN pesado
            $planes = DB::table('plan_trabajos')
                ->select('id', 'mes', 'anio', 'horas_requeridas', 'created_at', 'updated_at')
                ->where('user_id', $userId)
                ->whereNull('deleted_at')
                ->orderBy('anio', 'desc')
                ->orderBy('mes', 'desc')
                ->limit(20)
                ->get();
            
            // Devolver planes con valores de progreso por defecto
            $response = [];
            foreach ($planes as $plan) {
                $response[] = [
                    'id' => (int)$plan->id,
                    'mes' => (int)$plan->mes,
                    'anio' => (int)$plan->anio,
                    'horas_requeridas' => (int)$plan->horas_requeridas,
                    'horas_completadas' => 0, // Se calculará en background
                    'horas_reales' => 0,
                    'horas_justificadas' => 0,
                    'progreso_porcentaje' => 0,
                    'created_at' => $plan->created_at,
                    'updated_at' => $plan->updated_at
                ];
            }
            
            return response()->json($response, 200);
            
        } catch (\Exception $e) {
            \Log::error('Error en indexWithProgress: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }

    // Crear un nuevo plan de trabajo
    public function store(Request $request)
    {
        $userData = $request->user;
        if (is_array($userData)) {
            $userData = (object) $userData;
        }
        $user = \App\Models\User::where('email', $userData->email)->first();
        $plan = PlanTrabajo::create([
            'user_id' => $user->id,
            'mes' => $request->input('mes'),
            'anio' => $request->input('anio'),
            'horas_requeridas' => $request->input('horas_requeridas'),
        ]);
        return response()->json($plan, 201);
    }

    // Progreso de un plan de trabajo - OPTIMIZADO
    public function progreso(Request $request, $id)
    {
        try {
            $userData = $request->user;
            if (is_array($userData)) {
                $userData = (object) $userData;
            }
            
            $userEmail = $userData->email;
            
            // Query optimizada que obtiene plan y progreso en una consulta
            $resultado = DB::select("
                SELECT 
                    p.horas_requeridas,
                    p.mes,
                    p.anio,
                    COALESCE(SUM(h.Cantidad_Horas), 0) as horas_reales,
                    COALESCE(SUM(
                        CASE 
                            WHEN h.horas_equivalentes_calculadas IS NOT NULL 
                            THEN h.horas_equivalentes_calculadas - COALESCE(h.Cantidad_Horas, 0)
                            WHEN h.Monto_Compensario > 0 
                            THEN h.Monto_Compensario / COALESCE(h.valor_hora_al_momento, 1000)
                            ELSE 0 
                        END
                    ), 0) as horas_justificadas
                FROM plan_trabajos p
                INNER JOIN users u ON u.id = p.user_id AND u.email = ?
                LEFT JOIN horas_mensuales h ON (
                    h.email = ? AND 
                    h.mes = p.mes AND 
                    h.anio = p.anio AND
                    h.deleted_at IS NULL
                )
                WHERE p.id = ? AND p.deleted_at IS NULL
                GROUP BY p.id, p.horas_requeridas, p.mes, p.anio
                LIMIT 1
            ", [$userEmail, $userEmail, $id]);
            
            if (empty($resultado)) {
                return response()->json([
                    'horas_requeridas' => 0,
                    'horas_cumplidas' => 0,
                    'horas_reales' => 0,
                    'horas_justificadas' => 0,
                    'porcentaje' => 0
                ], 200);
            }
            
            $data = $resultado[0];
            $horasTotales = $data->horas_reales + $data->horas_justificadas;
            $porcentaje = $data->horas_requeridas > 0 ? 
                round(($horasTotales / $data->horas_requeridas) * 100, 2) : 0;
            
            $progreso = [
                'horas_requeridas' => (int)$data->horas_requeridas,
                'horas_cumplidas' => round($horasTotales, 2),
                'horas_reales' => round($data->horas_reales, 2),
                'horas_justificadas' => round($data->horas_justificadas, 2),
                'porcentaje' => $porcentaje
            ];
            
            return response()->json($progreso, 200);
            
        } catch (\Exception $e) {
            \Log::error("Error en progreso plan {$id}: " . $e->getMessage());
            return response()->json([
                'horas_requeridas' => 0,
                'horas_cumplidas' => 0,
                'horas_reales' => 0,
                'horas_justificadas' => 0,
                'porcentaje' => 0
            ], 200);
        }
    }

    // ENDPOINT SÚPER OPTIMIZADO: Todo en una sola consulta
    public function dashboard(Request $request)
    {
        $startTime = microtime(true);
        
        try {
            $userData = $request->user;
            if (is_array($userData)) {
                $userData = (object) $userData;
            }
            
            $userEmail = $userData->email;
            
            // CONSULTA ULTRA-OPTIMIZADA: Planes + Progreso + Estadísticas en 1 query
            $resultados = DB::select("
                SELECT 
                    p.id,
                    p.mes,
                    p.anio,
                    p.horas_requeridas,
                    p.created_at,
                    p.updated_at,
                    COALESCE(SUM(h.Cantidad_Horas), 0) as horas_reales,
                    COALESCE(SUM(
                        CASE 
                            WHEN h.horas_equivalentes_calculadas IS NOT NULL 
                            THEN h.horas_equivalentes_calculadas - COALESCE(h.Cantidad_Horas, 0)
                            WHEN h.Monto_Compensario > 0 
                            THEN h.Monto_Compensario / COALESCE(h.valor_hora_al_momento, 1000)
                            ELSE 0 
                        END
                    ), 0) as horas_justificadas,
                    COUNT(h.id) as total_registros
                FROM plan_trabajos p
                INNER JOIN users u ON u.id = p.user_id AND u.email = ?
                LEFT JOIN horas_mensuales h ON (
                    h.email = ? AND 
                    h.mes = p.mes AND 
                    h.anio = p.anio AND
                    h.deleted_at IS NULL
                )
                WHERE p.deleted_at IS NULL
                GROUP BY p.id, p.mes, p.anio, p.horas_requeridas, p.created_at, p.updated_at
                ORDER BY p.anio DESC, p.mes DESC
                LIMIT 50
            ", [$userEmail, $userEmail]);
            
            // Procesar resultados
            $planes = [];
            $estadisticas = [
                'total_planes' => 0,
                'planes_completados' => 0,
                'planes_activos' => 0,
                'horas_totales_requeridas' => 0,
                'horas_totales_cumplidas' => 0
            ];
            
            foreach ($resultados as $row) {
                $horasTotales = $row->horas_reales + $row->horas_justificadas;
                $porcentaje = $row->horas_requeridas > 0 ? 
                    round(($horasTotales / $row->horas_requeridas) * 100, 2) : 0;
                $completado = $porcentaje >= 100;
                
                // Construir objeto del plan
                $plan = [
                    'id' => (int)$row->id,
                    'mes' => (int)$row->mes,
                    'anio' => (int)$row->anio,
                    'horas_requeridas' => (int)$row->horas_requeridas,
                    'progreso' => [
                        'horas_cumplidas' => round($horasTotales, 2),
                        'horas_reales' => round($row->horas_reales, 2),
                        'horas_justificadas' => round($row->horas_justificadas, 2),
                        'porcentaje' => $porcentaje,
                        'completado' => $completado
                    ],
                    'estado' => $completado ? 'completado' : 'activo',
                    'total_registros' => (int)$row->total_registros,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at
                ];
                
                $planes[] = $plan;
                
                // Actualizar estadísticas
                $estadisticas['total_planes']++;
                $estadisticas['horas_totales_requeridas'] += $row->horas_requeridas;
                $estadisticas['horas_totales_cumplidas'] += $horasTotales;
                
                if ($completado) {
                    $estadisticas['planes_completados']++;
                } else {
                    $estadisticas['planes_activos']++;
                }
            }
            
            // Calcular estadísticas adicionales
            $estadisticas['porcentaje_global'] = $estadisticas['horas_totales_requeridas'] > 0 ? 
                round(($estadisticas['horas_totales_cumplidas'] / $estadisticas['horas_totales_requeridas']) * 100, 2) : 0;
            
            $endTime = microtime(true);
            $queryTime = round(($endTime - $startTime) * 1000, 2);
            
            // Respuesta optimizada
            $response = [
                'planes' => $planes,
                'estadisticas' => $estadisticas,
                'meta' => [
                    'total_planes' => count($planes),
                    'query_time_ms' => $queryTime,
                    'cached' => false,
                    'timestamp' => now()->toISOString()
                ]
            ];
            
            return response()->json($response, 200);
            
        } catch (\Exception $e) {
            \Log::error('Error en planes dashboard: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'planes' => [],
                'estadisticas' => [
                    'total_planes' => 0,
                    'planes_completados' => 0,
                    'planes_activos' => 0,
                    'horas_totales_requeridas' => 0,
                    'horas_totales_cumplidas' => 0,
                    'porcentaje_global' => 0
                ],
                'meta' => [
                    'total_planes' => 0,
                    'query_time_ms' => 0,
                    'cached' => false,
                    'error' => true,
                    'timestamp' => now()->toISOString()
                ]
            ], 200);
        }
    }
}
