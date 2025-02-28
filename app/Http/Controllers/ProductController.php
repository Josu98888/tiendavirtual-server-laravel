<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Models\Categorie;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth', ['except' => ['index', 'show', 'search', 'getImage', 'getProductsByCategory']]);
    }

    public function index()
    {
        $products = Product::all();
        $data = [
            'status' => 'success',
            'code' => 200,
            'products' => $products
        ];

        return response()->json($data, $data['code']);
    }

    // public function create()
    // {
    //     //
    // }

    public function store(Request $request)
    {

        // obtenemos los datos
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            // validamos los campos que nos llegan
            $validate = Validator::make($params_array, [
                'name' => 'required|unique:products',
                'categorieID' => 'required',
                'description' => 'required',
                'priceNow' => 'required',
                'priceBefore' => 'required',
                'stock' => 'required'
            ]);

            if ($validate->fails()) {
                $data = [
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'Error, no se pudo crear el producto.'
                ];
            } else {
                // instanciamos el producto con los datos y lo guardamos
                $product = new Product();
                $product->name = $params->name;
                $product->categorieID = $params->categorieID;
                $product->description = $params->description;
                $product->image = $params->image;
                $product->priceNow = $params->priceNow;
                $product->priceBefore = $params->priceBefore;
                $product->numSales = '0';
                $product->stock = $params->stock;
                $product->save();

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'product' => $product
                ];
            }
        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Error al enviar los datos del producto.'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function upload(Request $request)
    {
        // obtenemos la imagen
        $image = $request->file('file0');

        // validamos si nos llego la imagen
        if ($image) {
            // validamos la imagen
            $validate = Validator::make($request->all(), [
                'file0' => 'required|image|mimes:jpg,png,gif,jpeg'
            ]);

            if (!$validate->fails()) {
                // creamos el nombre de la imagen
                $imageName = time() . $image->getClientOriginalName();
                // almacenamos la imagen en el sorage
                Storage::disk('products')->put($imageName, File::get($image));

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'image' => $imageName
                ];
            } else {
                $data = [
                    'status' => 'error',
                    'code' => 200,
                    'message' => 'Error, la imagen enviada es incorrecta.'
                ];
            }
        } else {
            $data = [
                'status' => 'error',
                'code' => '404',
                'message' => 'Error, no se ha enviado la imagen.'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function show(string $id)
    {
        // obtenemos el producto
        $product = Product::where('id', $id)->first();
        // validamos si existe el producto
        if (is_object($product)) {
            $data = [
                'status' => 'success',
                'code' => 200,
                'product' => $product
            ];
        } else {
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'Error, el producto no existe.'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function getImage($filename) {
        // verificamos si la imagen existe
        $isset = Storage::disk('products')->exists($filename);

        if($isset) {
            // obtenemos la imagen 
            $file = Storage::disk('products')->get($filename);

            return new Response($file, 200);
        } else {
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'Error, la imagen no existe.'
            ];

            return response()->json($data, $data['code']);
        }
    }

    public function search(string $text)
    {
        // buscamos todos los productos coincidentes con text
        $products = Product::whereRaw("SOUNDEX(name) = SOUNDEX(?)", [$text])
            ->orWhere('name', 'LIKE', '%'.$text.'%')
            ->orderBy('id', 'desc')
            ->get();

        $data = [
            'status' => 'success',
            'code' => 200,
            'products' => $products
        ];

        return response()->json($data, $data['code']);
    }

    // public function edit(string $id)
    // {
    //     //
    // }

    public function update(Request $request, string $id)
    {
        //obtenemos los datos
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        // verificamos si nos llego los datos
        if (!empty($params_array)) {
            // validamos los datos 
            $validate = Validator::make($params_array, [
                'name' => 'required',
                'categorieID' => 'required',
                'description' => 'required',
                // 'image' => 'required',
                'priceNow' => 'required',
                'priceBefore' => 'required'
            ]);

            // validamos si hubo fallas en la validacion
            if ($validate->fails()) {
                $data = [
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'Error, los datos enviados son incorrectos.'
                ];
            } else {
                // eliminamos los campos que no se deben actualizar
                unset($params_array['id']);
                unset($params_array['created_at']);

                // bucamos el producto a actualizar
                $product = Product::where('id', $id)->first();

                // validamos si el producto existe
                if (is_object($product) && !empty($product)) {
                    // actualizamos el producto
                    $product->update($params_array);

                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'changes' => $params_array
                    ];
                } else {
                    $data = [
                        'status' => 'error',
                        'code' => 404,
                        'message' => 'Error, el producto no existe.'
                    ];
                }
            }
        } else {
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'Error, los datos no se han enviado.'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function destroy(string $id)
    {
        // buscamos el producto a eliminar
        $product = Product::where('id', $id)->first();
        // buscamos las ventas
        // $sales_detail = Sale_Detail::where('idProduct', $id)->get();

        // if($sales_detail && count($sales_detail) >= 1 ) {
        //     foreach($sales_detail as $sale_detail) {
        //         $sale_detail->delete();
        //     }
        // }

        // validamos si el producto existe
        if (is_object($product) && !empty($product)) {
            // eliminamos el producto
            $product->delete();

            $data = [
                'status' => 'success',
                'code' => 200,
                'product' => $product
            ];
        } else {
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'Error, el producto no existe.'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function getProductsByCategory($id) {
        // obtenemos los productos por categoria
        $products = Product::where('categorieId', $id)->get();
        // obtenemos el titulo de las categorias
        $categorie = Categorie::where('id', $id)->first();

        if(!empty($products)) {
            $data = [
                'status' => 'success',
                'code' => 200,
                'categorie' => $categorie->name,
                'products' => $products
            ];
        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Error, no existe la categoria.'
            ];
        }

        return response()->json($data, $data['code']);
    }
}
