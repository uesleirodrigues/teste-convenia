<?php

namespace App\Http\Controllers;

use App\Models\Collaborator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CollaboratorImport;

class CollaboratorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api'); // Garantir que o usuário está autenticado
    }

    /**
     * Lista todos os colaboradores do usuário autenticado.
     */
    public function index(): JsonResponse
    {
        $collaborators = Auth::user()->collaborators; // Relacionamento no modelo User
        return response()->json($collaborators);
    }

    /**
     * Cadastra um novo colaborador.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:collaborators,email',
            'cpf'   => 'required|string|unique:collaborators,cpf|regex:/^\d{11}$/',
            'city'  => 'required|string|max:100',
            'state' => 'required|string|max:2',
        ]);

        $collaborator = Auth::user()->collaborators()->create($request->all());
        return response()->json($collaborator, 201);
    }

    /**
     * Atualiza um colaborador existente.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $collaborator = Auth::user()->collaborators()->findOrFail($id);

        $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:collaborators,email,' . $id,
            'cpf'   => 'sometimes|string|regex:/^\d{11}$/',
            'city'  => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:2',
        ]);

        $collaborator->update($request->all());
        return response()->json($collaborator);
    }

    /**
     * Exclui um colaborador.
     */
    public function destroy(int $id): JsonResponse
    {
        $collaborator = Auth::user()->collaborators()->findOrFail($id);
        $collaborator->delete();
        return response()->json(null, 204);
    }

    /**
     * Importa colaboradores de um arquivo CSV e envia e-mail de notificação.
     */
    public function import(Request $request): JsonResponse
    {
        // Validação do arquivo
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:2048',
        ]);

        try {
            // Processando o arquivo CSV
            Excel::import(new CollaboratorImport(Auth::user()), $request->file('file'));

            // Enviar o e-mail de notificação para o usuário após o processamento
            \Mail::to(Auth::user()->email)->send(new \App\Mail\ImportNotificationMail());

            return response()->json(['message' => 'Processamento realizado com sucesso!'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao importar arquivo', 'details' => $e->getMessage()], 500);
        }
    }
}
