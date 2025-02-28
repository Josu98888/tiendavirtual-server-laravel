<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//IMPORTAMOS EL VALIDATORS PARA VALIDAR LOS DATOS DEL FORMULARIO
use Illuminate\Support\Facades\Validator;
//IMPORTAMOS EL MODELO DE USUARIO PARA CREARLO
use App\Models\User;
// importo el helper de jwt
use App\Helpers\JwtAuth;
use Illuminate\Contracts\Cache\Store;
// importo hash para cifrar la contraseña
use Illuminate\Support\Facades\Hash;
// para las imagenes
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
// para generar respuestas de descarga de archivos
use Illuminate\Http\Response;

class UserController extends Controller
{
    //hacemos un metodo de prueba
    public function prueba()
    {
        return 'El controlador UserController funciona correctamente';
    }

    public function register(Request $request)
    {
        //recogemos los datos del formulario que nos llega en formato json
        $json = $request->input('json', null);
        //decodificamos el json para poder usarlo en php
        $params = json_decode($json);
        //creamos un array del json decodificado
        $params_array = json_decode($json, true);

        //validamos que los datos no esten vacios
        if (!empty($params) && !empty($params_array)) {
            //limpiamos los datos
            $params_array = array_map('trim', $params_array);
            //validamos los datos con el Validator
            $validate = Validator::make($params_array, [
                'name' => 'required|alpha',
                'lastname' => 'required|alpha',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8'
            ]);

            //si la validacion falla
            if ($validate->fails()) {
                $data = [
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El usuario no se ha creado',
                    'errors' => $validate->errors()
                ];
            } else {
                //si la validacion es correcta
                //ciframos la contraseña
                $pwd = Hash::make($params->password);
                //creamos el usuario
                $user = new User();
                $user->name = $params_array['name'];
                $user->lastname = $params_array['lastname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';
                $user->image = '';
                //guardamos el usuario
                $user->save();

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario se ha creado correctamente',
                    'user' => $user
                ];
            }
        } else {
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'Los datos enviados no son correctos',
                'params' => $params,
                'params_array' => $params_array
            ];
        }

        //retornamos la respuesta en formato json
        return response()->json($data, $data['code']);
    }

    public function login(Request $request)
    {
        $JwtAuth = new JwtAuth();

        // Recogemos los datos del formulario en formato JSON
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        // Validamos los datos
        $validate = Validator::make($params_array, [
            'email' => 'required|email|exists:users,email',
            'password' => 'required'
        ]);

        // Si la validación falla
        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => 'Error, el usuario no se ha podido loguear.',
                'errors' => $validate->errors()
            ], 400);
        }

        // Buscar usuario en la base de datos
        $user = User::where('email', $params->email)->first();

        // Verificar si la contraseña es incorrecta
        if (!Hash::check($params->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'code' => 401,
                'message' => 'La contraseña es incorrecta.'
            ], 401);
        }

        // Intentamos realizar el login
        if (isset($params->email) && isset($params->password)) {
            $getToken = isset($params->getToken) ? $params->getToken : null;

            // Llamada a la función de autenticación
            $signup = $JwtAuth->signup($params->email, $params->password, $getToken);

            // Login exitoso, devolvemos el token o los datos
            return response()->json($signup, 200);
        }

        // Si faltan las credenciales
        return response()->json([
            'status' => 'error',
            'code' => 400,
            'message' => 'Los datos proporcionados son incompletos.'
        ], 400);
    }

    public function update(Request $request)
    {
        $token = $request->header('Authorization');

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if ($checkToken && !empty($params_array)) {
            // obtener el user identificado
            $user = $jwtAuth->checkToken($token, true);

            // validar los datos
            $validate = Validator::make($params_array, [
                'name' => 'required|alpha',
                'lastname' => 'required|alpha',
                'email' => 'required|email|unique:users' . $user->sub
            ]);

            // eliminar los campos que no deseo actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            // actualizamos el user
            $user_update = User::where('id', $user->sub)->update($params_array);

            $data = [
                'status' => 'success',
                'code' => 200,
                'user' => $user,
                'chages' => $params_array
            ];
        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'El usuario no esta identificado.'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function upload(Request $request)
    {
        // obtenemos la imagen
        $image = $request->file('file0');

        // validacion de la imagen
        $validate = Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        // guardamos imagen si no falla
        if (!$image || $validate->fails()) {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Error al subir la imagen',
            ];
        } else {
            // obtenemos el nombre de la imagen y lo hacemos irrepetible
            $image_name = time() . $image->getClientOriginalName();
            // guardamos la img en el disco users
            Storage::disk('users')->put($image_name, File::get($image));

            $data = [
                'status' => 'success',
                'code' => 200,
                'image' => $image_name
            ];
        }
        // retornamos la data, el codigo de la data y la cabecera tipo archivo de texto
        return response()->json($data, 200);
    }

    public function getImage($filename)
    {
        // verificamos si esiste la imagen
        $isset = Storage::disk('users')->exists($filename);
        if ($isset) {
            //obtenemos la imagen
            $file = Storage::disk('users')->get($filename);
            // retornamos la imagen
            return new Response($file, 200);
        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'La imagen no existe.'
            ];

            return response()->json($data, $data['code']);
        }
    }

    public function detail($id)
    {
        // obtenemos al user con el id
        $user = User::find($id);

        if (is_object($user)) {
            $data = [
                'status' => 'success',
                'code' => 200,
                'user' => $user
            ];
        } else {
            $data = [
                'status' => 'succes',
                'code' => 400,
                'message' => 'El usuario no existe.'
            ];
        }

        return response()->json($data, $data['code']);
    }
}
