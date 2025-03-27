<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A lista de exceções que são reportadas.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * O método de renderização para exceções.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Throwable  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        // Se for uma exceção de validação, retorna erro em JSON
        if ($exception instanceof ValidationException) {
            return response()->json([
                'errors' => $exception->errors()  // Retorna os erros de validação
            ], 422);
        }

        // Para outras exceções, apenas chama o método padrão
        return parent::render($request, $exception);
    }
}
