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
        if (app()->environment('testing')) {
            $user = \App\Models\User::where('email', 'user@test.com')->first();
            if ($user) {
                $request->merge(['user' => [
                    'email' => $user->email,
                    'name' => $user->name,
                    'id' => $user->id
                ]]);
            } else {
                $request->merge(['user' => [
                    'email' => 'user@test.com',
                    'name' => 'Test User',
                    'id' => 1
                ]]);
            }
            return $next($request);
        }

        $token = $request->header('Authorization');
        if($token == null)
            return response()->json(["error" => "Not authenticated"],401);

        $validacion = Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->get('http://127.0.0.1:8000/api/validate');

        if($validacion->status() != 200)
            return response()->json(["error" => "Invalid Token"],401);

        $userData = $validacion->json();
        if (!isset($userData['user']['email'])) {
            return response()->json(["error" => "No se pudo obtener el email del usuario autenticado"], 401);
        }
        $request->merge(['user' => [
            'email' => $userData['user']['email'],
            'name' => $userData['user']['name'],
            'id' => $userData['user']['id']
        ]]);
        return $next($request);
    }
}
