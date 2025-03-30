<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      title="API de Colaboradores",
 *      version="1.0.0",
 *      description="Documentação da API para gerenciamento de colaboradores."
 * ),
 * @OA\PathItem(path="/api")
 */


abstract class Controller extends BaseController
{
    //
}
