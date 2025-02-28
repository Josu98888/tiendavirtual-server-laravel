<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\JwtAuth;
use Illuminate\Support\Facades\Validator;
use App\Models\Sale_Detail;
use App\Models\Sale;
use App\Models\Product;


class SaleController extends Controller
{
    private function getIdentity(Request $request)
    {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

    public function index(Request $request) {
        // obtenemos el user identificado
        $user = $this->getIdentity($request);
        // obtenemos las ventas
        $sales = Sale::where('idUser', $user->sub)->get();

        // $quantity = [];
        // $products = [];
        // $subTotalItems = [];

        // Iterar sobre los productos del carrito
        foreach ($sales as $item) {
            $products[] = $item->product;
            $quantity[] = $item->quantity;
            $date[] = $item->created_at->format('Y-m-d H:i:s');
            $subtotals = $item->product->priceNow * $item->quantity;
            $subTotalItems[] = $subtotals;
        }

        // total de productos en el carrito
        $totalQuantity = array_sum($quantity);

        $data = [
            'status' => 'success',
            'code' => 200,
            'cart' => $sales,
            'products' => $products,
            'date' => $date,
            'quantityTotal' => $totalQuantity,
            'quantityProduct' => $quantity,
            'subtotals' => $subTotalItems,
        ];

        return response()->json($data, $data['code']);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //obtenemos el user identificado
        $user = $this->getIdentity($request);
        // obtenemos el carrito del user
        $cart = Sale_Detail::where('idUser', $user->sub)->get();

        if (!$cart->isEmpty()) {
            foreach ($cart as $item) {
                // Obtener el producto
                $product = Product::find($item->idProduct);

                if ($product) {
                    // Verificar si hay suficiente stock
                    if ($product->stock >= $item->quantity) {
                        // Reducir el stock
                        $product->stock -= $item->quantity;
                        $product->save();

                        // Registrar la venta
                        $sale = new Sale();
                        $sale->idUser = $item->idUser;
                        $sale->idProduct = $item->idProduct;
                        $sale->quantity = $item->quantity ;
                        $sale->save();

                        $data = [
                            'status' => 'success',
                            'code' => 200,
                            'sale' => $cart
                        ];
                    } else {
                        $data = [
                            'status' => 'error',
                            'code' => 400,
                            'message' => 'No hay suficiente stock del producto: ' . $product->name
                        ];
                        break;
                    }
                }
            }
        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Su carrito esta vacío.'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function show(string $id, Request $request)
    {
        // obtenemos al user identificado
        $user = $this->getIdentity($request);
        // obtenemos la venta
        $sale = Sale::where('idUser', $user->sub)->where('idProduct', $id)->first();

        // verificamos si existe
        if(!empty($sale)) {
            $data = [
                'status' => 'success',
                'code' => 200,
                'sale' => $sale
            ];
        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'No se ha realizado ninguna venta.'
            ];
        }

        return response()->json($data, $data['code']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
