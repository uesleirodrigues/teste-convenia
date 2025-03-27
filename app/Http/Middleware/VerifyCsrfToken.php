<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * Remover algumas rotas da verificação CSRF, pois ela invalida a requisição de alguma forma
     *
     * @var array
     */
    protected $except = [
        'api/*',  // Ignorar todas as rotas dentro de /api/
        'login',  // Ignorar a rota de login
    ];
}
