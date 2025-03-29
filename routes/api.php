<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CollaboratorController;
use App\Http\Controllers\CollaboratorImportController;

// Rota para autenticação (login/logout)
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);

// Rotas protegidas por autenticação JWT
Route::middleware('jwt.auth')->group(function () {

    // Rota para criar um colaborador
    Route::post('/collaborators', [CollaboratorController::class, 'store']);

    // Rota para listar os colaboradores do usuário logado
    \Log::info('Acessando a rota de colaboradores');
    Route::get('/collaborators', [CollaboratorController::class, 'index']);

    // Rota para editar um colaborador (requisição PUT)
    Route::put('/collaborators/{id}', [CollaboratorController::class, 'update']);

    // Rota para deletar um colaborador
    Route::delete('/collaborators/{id}', [CollaboratorController::class, 'destroy']);

    // Rota para importar do CSV
    Route::post('/collaborators/import', [CollaboratorController::class, 'import']);
    //Route::post('/collaborators/import', [CollaboratorController::class, 'import'])->middleware('auth:api');

});
