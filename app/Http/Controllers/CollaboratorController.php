<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Collaborator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreCollaboratorRequest;
use App\Http\Requests\UpdateCollaboratorRequest;
use App\Http\Resources\CollaboratorResource;
use App\Services\CollaboratorImportService;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CollaboratorImport;

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
        $userId = Auth::id();
        $cacheKey = 'collaborators_' . $userId;

        // Cache dos colaboradores por 10 minutos
        $collaborators = Cache::remember($cacheKey, 10 * 60, function () {
            \Log::info('Consulta ao banco de dados para colaboradores do usuário ' . Auth::id());
            return Auth::user()->collaborators;
        });

        \Log::info('Dados de colaboradores para o usuário ' . Auth::id() . ' foram ' . (Cache::has($cacheKey) ? 'obtidos do cache' : 'buscados do banco'));

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

            // Invalida o cache depois de criar um novo colaborador
            $this->invalidateCollaboratorsCache();

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

            // Invalida o cache depois de atualizar
            $this->invalidateCollaboratorsCache();

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

        // Invalida o cache depois de excluir
        $this->invalidateCollaboratorsCache();

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
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getClientMimeType();
            $path = 'temp_imports/' . uniqid() . '_' . $originalName;
            Storage::disk('local')->put($path, file_get_contents($file));

            // Despacha o Job
            \App\Jobs\ImportCollaborators::dispatch(
                Auth::user(),
                Storage::disk('local')->path($path),
                $originalName,
                $mimeType
            );

            return response()->json(['message' => 'Importação iniciada em segundo plano. Você será notificado ao ser finalizada a importação.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao iniciar importação', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Método auxiliar para invalidar o cache de colaboradores do usuário atual
     */
    protected function invalidateCollaboratorsCache(): void
    {
        $cacheKey = 'collaborators_' . Auth::id();
        Cache::forget($cacheKey);
    }
}