<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Categorie;

class CategorieController extends Controller
{
    public function __construct(){
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }
    
    public function index()
    {
        $categories = Categorie::all();

        $data = [
            'status' => 'success',
            'code' => 200,
            'categories' => $categories
        ];

        return response()->json($data, $data['code']);
    }

    // public function create()
    // {
    //     //
    // }

    public function store(Request $request)
    {
        // obtenemos lo que nos llega 
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if(!empty($params_array)) {
            // validamos los datos
            $validate = Validator::make($params_array, [
                'name' => 'required'
            ]);

            if($validate->fails()) {
                $data = [
                    'status' => 'success',
                    'code' => 404,
                    'message' => 'Error al crear la categoria.'
                ];
            } else {
                // creamos la categoria
                $categorie = new Categorie();
                $categorie->name = $params_array['name'];
                $categorie->save();

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Exito al crear la categoria.',
                    'categorie' => $categorie
                ];
            }
        } else {
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'Error al enviar la categoria.'
            ];
        }

        return response()->json($data, $data['code']);
    }

    

    // public function edit(string $id)
    // {
    //     //
    // }

    public function update(Request $request, string $id)
    {
        // recibimos los datos
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if(!empty($params_array)) {
            // validar los datos
            $validate = Validator::make($params_array, [
                'name' => 'required'
            ]);

            if($validate->fails()) {
                $data = [
                    'status' => 'success',
                    'code' => 404,
                    'message' => 'Error, la categoria no se ha guardado.'
                ];
            } else {
                // eliminamos lo que no se actualiza
                unset($params_array['id']);
                unset($params_array['created_at']);

                // buscamos la categoria
                $categorie = Categorie::where('id', $id)->first();

                if(!empty($categorie) && is_object($categorie)){
                    // actualizamos la categoria
                    $categorie->update($params_array);

                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'changes' => $params_array
                    ];
                } else {
                    $data = [
                        'status' => 'error',
                        'code' => 404,
                        'message' => 'Error la categoria no existe.'
                    ];
                }
            }

        } else {
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'Error, los datos se han enviado incorrectamente.'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function destroy(string $id)
    {
        // obtenemos la categoria a eliminar
        $categorie = Categorie::where('id', $id)->first();

        if(is_object($categorie) && !empty($categorie)) {
            // eliminamos la categoria
            $categorie->delete();

            $data = [
                'status' => 'success',
                'code' => 200,
                'categorie' => $categorie
            ];
        } else {
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'Error, la categoria que desea eliminar no existe.'
            ];
        }

        return response()->json($data, $data['code']);
    }
}
