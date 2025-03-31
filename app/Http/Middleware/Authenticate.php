<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response; // Importe a classe Response

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['status' => Response::HTTP_UNAUTHORIZED, 'message' => 'Token inválido'], Response::HTTP_UNAUTHORIZED);
            }
        } catch (\Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return response()->json(['status' => Response::HTTP_UNAUTHORIZED, 'message' => 'Token inválido'], Response::HTTP_UNAUTHORIZED);
            } elseif ($e instanceof TokenExpiredException) {
                return response()->json(['status' => Response::HTTP_UNAUTHORIZED, 'message' => 'Token expirado'], Response::HTTP_UNAUTHORIZED);
            } elseif (!$request->header('Authorization')) {
                return response()->json(['status' => Response::HTTP_UNAUTHORIZED, 'message' => 'Token não fornecido'], Response::HTTP_UNAUTHORIZED);
            } else {
                return response()->json(['status' => Response::HTTP_UNAUTHORIZED, 'message' => 'Erro de autenticação do token'], Response::HTTP_UNAUTHORIZED);
            }
        }

        return $next($request);
    }
}