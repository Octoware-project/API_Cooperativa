<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Conexion;

class UsuariosApiService
{
    public function ObtenerUsuarioDesdeAPI_Usuarios($token) {
        $cliente = new Cliente();
        try {
            $respuesta = $cliente->get('https://github.com/Octoware-project/API_Usuarios.git', [
                'headers' => [
                    'Authorization' => "Bearer $token"
                ]]);

                $usuario = json_decode($respuesta->getBody(), true);

            if (isset($usuario['id'])) {
                return $usuario;
            } else {
                return null; 
            }     
        } catch (\Exception $e) {
            return null;
        }
    }
}