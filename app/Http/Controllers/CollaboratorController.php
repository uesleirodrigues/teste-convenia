<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Collaborator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreCollaboratorRequest;
use App\Http\Requests\UpdateCollaboratorRequest;
use App\Http\Resources\CollaboratorResource;
use App\Services\CollaboratorImportService;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Collaborators", description="Gerenciamento de Colaboradores")
 */
class CollaboratorController extends Controller
{
    protected $importService;

    public function __construct(CollaboratorImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * @OA\Get(
     *     path="/api/collaborators",
     *     summary="Lista todos os colaboradores",
     *     tags={"Collaborators"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de colaboradores",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Collaborator")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Erro no servidor")
     * )
     */
    public function index(): JsonResponse
    {
        $collaborators = Auth::user()->collaborators;
        return response()->json([
            'data' => CollaboratorResource::collection($collaborators)
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/collaborators",
     *     summary="Cria um novo colaborador",
     *     tags={"Collaborators"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Collaborator")
     *     ),
     *     @OA\Response(response=201, description="Colaborador criado com sucesso", @OA\JsonContent(ref="#/components/schemas/Collaborator")),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=500, description="Erro no servidor")
     * )
     */
    public function store(StoreCollaboratorRequest $request): JsonResponse
    {
        try {
            $collaborator = Auth::user()->collaborators()->create($request->validated());
            return response()->json(['data' => new CollaboratorResource($collaborator)], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Erro de validação', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao criar colaborador', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/collaborators/{id}",
     *     summary="Atualiza um colaborador",
     *     tags={"Collaborators"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do colaborador",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Collaborator")
     *     ),
     *     @OA\Response(response=200, description="Colaborador atualizado", @OA\JsonContent(ref="#/components/schemas/Collaborator")),
     *     @OA\Response(response=404, description="Colaborador não encontrado"),
     *     @OA\Response(response=500, description="Erro no servidor")
     * )
     */
    public function update(UpdateCollaboratorRequest $request, int $id): JsonResponse
    {
        try {
            $collaborator = Auth::user()->collaborators()->findOrFail($id);
            $collaborator->update($request->validated());
            return response()->json(['data' => new CollaboratorResource($collaborator)]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Erro de validação', 'errors' => $e->errors()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Colaborador não encontrado'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao atualizar colaborador', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/collaborators/{id}",
     *     summary="Exclui um colaborador",
     *     tags={"Collaborators"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do colaborador",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=204, description="Colaborador excluído"),
     *     @OA\Response(response=404, description="Colaborador não encontrado"),
     *     @OA\Response(response=500, description="Erro no servidor")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $collaborator = Auth::user()->collaborators()->findOrFail($id);
        $collaborator->delete();
        return response()->json(null, 204);
    }

    /**
     * @OA\Post(
     *     path="/api/collaborators/import",
     *     summary="Importa uma lista de colaboradores via arquivo CSV/Excel",
     *     tags={"Collaborators"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="file", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Arquivo importado com sucesso"),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=500, description="Erro ao importar arquivo")
     * )
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:csv,xlsx,xls|max:2048']);
        try {
            $result = $this->importService->importAndNotify(Auth::user(), $request->file('file'));
            return response()->json(['message' => $result['message']], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao importar arquivo', 'details' => $e->getMessage()], 500);
        }
    }
}
