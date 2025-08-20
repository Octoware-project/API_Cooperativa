<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class ValidarUsuario
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Revisar si viene la cabecera Authorization
        if (!$request->hasHeader('Authorization')) {
            return response()->json(["mensaje" => "Inhabilitado: sin token"], 401);
        }

        // Validar el token en la otra API
        $validacion = Http::withHeaders([
            'Authorization' => $request->header('Authorization')
        ])->get('http://127.0.0.1:8001/api/validate'); // puerto de la otra API

        if ($validacion->status() !== 200) {
            return response()->json(["mensaje" => "Inhabilitado: token inválido"], 401);
        }

        // Guardar el usuario validado en el request
        $request->attributes->set('user', $validacion->json());

        // Si la ruta es solo para probar token, devolver la respuesta directamente desde middleware
        if ($request->is('api/test-token')) {
            return response()->json([
                'mensaje' => 'Token válido',
                'usuario' => $request->attributes->get('user')
            ]);
        }

        return $next($request);    
    }
}
