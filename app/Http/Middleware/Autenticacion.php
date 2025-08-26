<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;

class Autenticacion
{
    
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization');
        if($token == null)
            return response(["error" => "Not authenticated"],401);

        $validacion = Http::withHeaders([
    'Authorization' => $token,
    'Accept' => 'application/json',
    'Content-Type' => 'application/json'
    ])->get('http://127.0.0.1:8000/api/validate'); // apunta a la API 1

        if($validacion->status() != 200)
            return response(["error" => "Invalid Token"],401);

        $request -> merge(['user' => $validacion->json()]);
        return $next($request);
    }
}
