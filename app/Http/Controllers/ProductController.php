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

    public function store(Request $request)
    {
        // validamos los campos que nos llegan
        $validate = Validator::make($request->all(), [
            'name' => 'required|unique:products',
            'categorieID' => 'required',
            'description' => 'required',
            'priceNow' => 'required',
            'priceBefore' => 'required',
            'stock' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validate->fails()) {
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'Error, no se pudo crear el producto.'
            ];
        } else {
            // Crear una nueva instancia del producto
            $product = new Product();
            $product->name = $request->input('name');
            $product->categorieID = $request->input('categorieID');
            $product->description = $request->input('description');
            $product->priceNow = $request->input('priceNow');
            $product->priceBefore = $request->input('priceBefore');
            $product->numSales = 0;
            $product->stock = $request->input('stock');

            // manejo de la imagen
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $image_name = time() . $image->getClientOriginalName();
                Storage::disk('products')->put($image_name, File::get($image));
                $product->image = $image_name;
            }
            // Guardar el producto en la base de datos
            $product->save();

            $data = [
                'status' => 'success',
                'code' => 200,
                'product' => $product
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function update(Request $request, string $id)
    {
        // validamos los datos 
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'categorieID' => 'required',
            'description' => 'required',
            'priceNow' => 'required',
            'priceBefore' => 'required'
        ]);

        // validamos si hubo fallas en la validación
        if ($validate->fails()) {
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'Error, los datos enviados son incorrectos.'
            ];
        } else {
            // buscamos el producto a actualizar
            $product = Product::find($id);

            // validamos si el producto existe
            if (is_null($product)) {
                $data = [
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'Error, el producto no existe.'
                ];
            } else {
                // actualizamos los campos del producto
                $product->name = $request->input('name');
                $product->categorieID = $request->input('categorieID');
                $product->description = $request->input('description');
                $product->priceNow = $request->input('priceNow');
                $product->priceBefore = $request->input('priceBefore');
                $product->stock = $request->input('stock'); // Si tienes stock también en el formulario

                // Manejo de la imagen
                if ($request->hasFile('image')) {
                    // eliminamos la imagen anterior si existe
                    if ($product->image && Storage::disk('products')->exists($product->image)) {
                        Storage::disk('products')->delete($product->image);
                    }

                    // guardamos la nueva imagen
                    $image = $request->file('image');
                    $image_name = time() . $image->getClientOriginalName();
                    Storage::disk('products')->put($image_name, File::get($image));
                    $product->image = $image_name;  // Asignamos la nueva imagen al producto
                }

                // guardamos los cambios en el producto
                $product->save();

                // respuesta exitosa
                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'changes' => $product
                ];
            }
        }

        // único return con la respuesta
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

    public function getImage($filename)
    {
        $path = storage_path("app/public/products/{$filename}");

            if (File::exists($path)) {
                $file = File::get($path);
                $mimeType = File::mimeType($path); // Obtiene el tipo MIME correcto
        
                return response($file, 200)->header("Content-Type", $mimeType);
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
            ->orWhere('name', 'LIKE', '%' . $text . '%')
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

    public function getProductsByCategory($id)
    {
        // obtenemos los productos por categoria
        $products = Product::where('categorieId', $id)->get();
        // obtenemos el titulo de las categorias
        $categorie = Categorie::where('id', $id)->first();

        if (!empty($products)) {
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
