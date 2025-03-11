<?php

use App\Http\Controllers\CategorieController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PruebaController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\apiAuthMiddleware;
use App\Http\Controllers\SaleController; 
use App\Http\Controllers\SaleDetailController;
use App\Http\Middleware\HandleImageCors;

//pruebas
//creamos la ruta para el controller y su metodo 
Route::get('/', [PruebaController::class, 'index']);
Route::get('/user-prueba', [UserController::class, 'prueba']);

//rutas del controlador de usuario
Route::post('/api/register', [UserController::class, 'register']);
Route::post('/api/login', [UserController::class, 'login']);
Route::post('/api/update' , [UserController::class, 'update'])->middleware(HandleImageCors::class);
Route::post('/api/user/upload', [UserController::class, 'upload'])->middleware(apiAuthMiddleware::class);
Route::get('/api/user/avatar/{filename}', [UserController::class, 'getImage']);
Route::get('/api/user/detail/{id}', [UserController::class, 'detail']);

// rutas del controller de categoria
Route::resource('/api/categorie', CategorieController::class);

// rutas del controller Product
Route::resource('/api/product', ProductController::class);
Route::post('/api/product/upload', [ProductController::class, 'upload']);
Route::get('/api/product/search/{text}', [ProductController::class, 'search']);
Route::get('/api/product/image/{filename}', [ProductController::class, 'getImage']);
Route::get('/api/product/getProductsByCategory/{id}', [ProductController::class, 'getProductsByCategory']);

// rutas del controller saleDetail 
Route::resource('/api/saleDetail', SaleDetailController::class);
Route::delete('/api/delete', [SaleDetailController::class, 'delete']);

//rutas del controller sale
Route::resource('/api/sale', SaleController::class);
