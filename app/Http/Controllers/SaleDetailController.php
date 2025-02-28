<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\JwtAuth;
use App\Models\Sale_Detail;
use App\Models\Product;

class SaleDetailController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth');
    }

    private function getIdentity(Request $request)
    {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

    public function index(Request $request)
    {
        // obtenemos el user identificado
        $user = $this->getIdentity($request);
        // obtenemos los productos del carrito del user identificado
        $cart = Sale_Detail::where('idUser', $user->sub)->get();

        $total = 0;
        $idProducts = [];
        $quantity = [];
        $products = [];
        $subTotalItems = [];

        // Iterar sobre los productos del carrito
        foreach ($cart as $item) {
            $products[] = $item->product;
            $quantity[] = $item->quantity;
            $idProducts[] = $item->id;

            $subtotals = $item->product->priceNow * $item->quantity;
            $subTotalItems[] = $subtotals;
        }

        // suma total de los productos
        $total = array_sum($subTotalItems);
        // total de productos en el carrito
        $totalQuantity = array_sum($quantity);

        $dataProducts = implode('-', $idProducts);

        $data = [
            'status' => 'success',
            'code' => 200,
            'products' => $products,
            'cart' => $cart,
            'count' => $totalQuantity,
            'dataProducts' => $dataProducts,
            'quantity' => $quantity,
            'subtotals' => $subTotalItems,
            'total' => $total
        ];

        return response()->json($data, $data['code']);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        $user = $this->getIdentity($request);

        if (!empty($params_array)) {
            $validate = Validator::make($params_array, [
                'quantity' => 'required',
                'idProduct' => 'required'
            ]);

            if ($validate->fails()) {
                $data = [
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'No se ha guardado correctamente.'
                ];
            } else {
                $idProduct = $params_array['idProduct'];
                $quantity = $params_array['quantity'];
                $cartItem = Sale_Detail::where('idUser', $user->sub)
                    ->where('idProduct', $idProduct)
                    ->first();

                if ($cartItem) {
                    $cartItem->quantity += $quantity;
                    $cartItem->save();
                    $cart = $cartItem;

                } else {
                    $cart = new Sale_Detail();
                    $cart->idUser = $user->sub;
                    $cart->idProduct = $params_array['idProduct'];
                    $cart->quantity = $params_array['quantity'];
                    $cart->save();
                }

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'cart' => $cart
                ];
            }
        } else {
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'No se ha enviado nada.'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function show(string $id, Request $request)
    {
        //obtenemos al user identificado
        $user = $this->getIdentity($request);

        $cart = Sale_Detail::all()->where('idUser', $user->sub)->where('idProduct', $id);

        foreach ($cart as $car) {
            if ($car->id == $id) {
                return response()->json([
                    'status' => 'success',
                    'estado' => 'actualizar',
                    'cart' => $car
                ], 200);
            }
        }

        if ($cart) {
            return response()->json([
                'status' => 'success',
                'estado' => 'crear',
                'cart' => $cart
            ], 200);
        }
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id, Request $request)
    {
        //obtenemos el user identificado 
        $user = $this->getIdentity($request);
        $cart = Sale_Detail::where('idProduct', $id)->where('idUser', $user->sub)->first();

        if (!empty($cart)) {
            $cart->delete();

            $data = [
                'status' => 'success',
                'code' => 200,
                'cart' => $cart
            ];
        } else {
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'El producto no existe.'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function delete(Request $request)
    {
        // obtenemos el usuario identificado
        $user = $this->getIdentity($request);
        // obtenemos la coleccion de carrito
        $cart = Sale_detail::where('idUser', $user->sub)->get();

        // verificamos si no esta vacio
        if (!empty($cart)) {
            $cart->each->delete();

            $data = [
                'status' => 'success',
                'code' => 200,
                'cart' => $cart
            ];
        } else {
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'No se pudo eliminar el carrito.'
            ];
        }

        return response()->json($data, $data['code']);
    }
}
