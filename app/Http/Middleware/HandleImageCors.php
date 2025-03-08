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
            $response->headers->set('Access-Control-Allow-Origin', '*');  // Permite todos los dominios
            $response->headers->set('Access-Control-Allow-Methods', 'GET');  // Permite el método GET
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With');
        }

        return $response;
    }
}
