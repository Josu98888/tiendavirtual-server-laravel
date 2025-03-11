<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleImageCors
{
    /**
     * Maneja la solicitud y agrega los encabezados CORS para las imágenes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Verificar si el recurso solicitado es una imagen
        if ($request->is('imagenes/*')) {  // Ajusta la ruta según corresponda
            // Agregar encabezados CORS para permitir acceso a las imágenes
            $response->headers->set('Access-Control-Allow-Origin', 'https://tiendavirtual-client-angular.vercel.app');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    
        }

        return $response;
    }
}
