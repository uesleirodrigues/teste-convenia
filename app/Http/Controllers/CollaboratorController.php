<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Collaborator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreCollaboratorRequest;
use App\Http\Requests\UpdateCollaboratorRequest;
use App\Http\Resources\CollaboratorResource;
use App\Services\CollaboratorImportService;
use Illuminate\Validation\ValidationException;

class CollaboratorController extends Controller
{
    protected $importService;

    public function __construct(CollaboratorImportService $importService)
    {
        //$this->middleware('auth:api'); Tirei porquê causa redirecionamento indevido pra página de login e quebra as validações.
        $this->importService = $importService;
    }

    public function index(): JsonResponse
    {
        $collaborators = Auth::user()->collaborators;
        return response()->json([
            'data' => CollaboratorResource::collection($collaborators)
        ]);
    }

    public function store(StoreCollaboratorRequest $request): JsonResponse
    {
        try {
            // Validação
            $collaborator = Auth::user()->collaborators()->create($request->validated());

            // Retorna o colaborador criado com sucesso
            return response()->json([
                'data' => new CollaboratorResource($collaborator)
            ], 201);
        } catch (ValidationException $e) {
            // Captura erros de validação específicos
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Captura outros tipos de erros
            return response()->json([
                'message' => 'Erro ao criar colaborador',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateCollaboratorRequest $request, int $id): JsonResponse
    {
        try {
            // Busca o colaborador do usuário autenticado
            $collaborator = Auth::user()->collaborators()->findOrFail($id);

            // Atualiza o colaborador com os dados validados
            $collaborator->update($request->validated());

            // Retorna o colaborador atualizado com sucesso
            return response()->json([
                'data' => new CollaboratorResource($collaborator)
            ]);
        } catch (ValidationException $e) {
            // Captura erros de validação específicos
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Trata o caso de colaborador não encontrado
            return response()->json([
                'message' => 'Colaborador não encontrado',
            ], 404);
        } catch (\Exception $e) {
            // Captura outros tipos de erros
            return response()->json([
                'message' => 'Erro ao atualizar colaborador',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $collaborator = Auth::user()->collaborators()->findOrFail($id);
        $collaborator->delete();
        return response()->json(null, 204);
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:2048',
        ]);

        try {
            $result = $this->importService->importAndNotify(Auth::user(), $request->file('file'));
            return response()->json(['message' => $result['message']], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao importar arquivo', 'details' => $e->getMessage()], 500);
        }
    }
}