<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Conexion;

class UsuariosApiService
{
    public function ObtenerUsuarioDesdeAPI_Usuarios($token) {
        $token = request()->bearerToken();

        $response = http::withToken($token)
        ->get('https://127.0.0.1:8000/api/validate');

        if(!($response->successful() && isset($response['id']))){
            abort(401, 'Token inválido');
        } return $response ['id'];
    }
}
