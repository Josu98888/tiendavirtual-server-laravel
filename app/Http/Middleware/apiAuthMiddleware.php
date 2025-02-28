<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\JwtAuth;

class apiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // obtenemos la cabecera dondde esta el token
        $token = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $token);

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);
        if ($checkToken) {
            return $next($request);
        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'El usuario no esta identificado.',
                'token' => $token
            ];
        }
        return response()->json($data, $data['code']);
    }
}
