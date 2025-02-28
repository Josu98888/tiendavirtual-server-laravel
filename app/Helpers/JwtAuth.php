<?php

namespace App\Helpers;  // aca el  namespace es para que sepa que esta en la carpeta Helpers

use Firebase\JWT\JWT;  // se importa la libreria de JWT
use App\Models\User;  // se importa el modelo de User
use DomainException;
use Illuminate\Support\Facades\Auth;
use UnexpectedValueException;

class JwtAuth
{
    public $key;  // se declara la variable key

    public function __construct()
    {
        $this->key = 'esto_es_una_clave_super_secreta_99bb7766';  // se le asigna un valor a la variable key
    }

    public function signup($email, $password, $getToken = null)
    {
        //asignamos el user identificado
        $user = User::where('email', $email)->first();

        //si el usuario existe
        if ($user) {
            $token = [
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'lastname' => $user->lastname,
                'role' => $user->role,
                'image' => $user->image,
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60),
            ];

            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);

            return is_null($getToken) ? $jwt : $decoded;
        }

        return [
            'status' => 'error',
            'message' => 'Login incorrecto',
            'user' => $user
        ];
    }

    public function checkToken($jwt, $identity = false)
    {
        $auth = false;

        try {
            $jwt = str_replace('"', '', $jwt);
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (UnexpectedValueException $e) {
            $auth = false;
        } catch (DomainException $e) {
            $auth = false;
        }


        if (isset($decoded) && !empty($decoded) && is_object($decoded) && isset($decoded->sub)) {
            $auth = true;
        } else {
            $auth = false;
        }

        if($identity != false) {
            return $decoded ;
        } else {
            return $auth ;
        }
    }
}
