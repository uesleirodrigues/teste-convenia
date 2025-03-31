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

    /**
     * @OA\Get(
     *     path="/api/collaborators",
     *     summary="Lista todos os colaboradores",
     *     tags={"Collaborators"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de colaboradores",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Collaborator")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Token expirado ou inválido"),
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
     *     @OA\Response(
     *         response=201,
     *         description="Colaborador criado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Collaborator")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Erro de validação"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token expirado ou inválido"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro no servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Erro ao criar colaborador"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
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
     *     @OA\Response(
     *         response=200,
     *         description="Colaborador atualizado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Collaborator")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Colaborador não encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Colaborador não encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Erro de validação"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token expirado ou inválido"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao atualizar colaborador",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Erro ao atualizar colaborador"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
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
     *     @OA\Response(
     *         response=200,
     *         description="Colaborador excluído com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Colaborador excluído com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Colaborador não encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Colaborador não encontrado"),
     *             @OA\Property(property="details", type="string", example="Não foi possível encontrar um colaborador com ID: 1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token expirado ou inválido"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro no servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Erro ao excluir colaborador"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $collaborator = Auth::user()->collaborators()->findOrFail($id);
            $collaborator->delete();

            $this->invalidateCollaboratorsCache();

            return response()->json(['message' => 'Colaborador excluído com sucesso'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Colaborador não encontrado',
                'details' => "Não foi possível encontrar um colaborador com ID: {$id}"
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao excluir colaborador',
                'error' => $e->getMessage()
            ], 500);
        }
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
     *     @OA\Response(
     *         response=200,
     *         description="Arquivo importado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Importação iniciada em segundo plano. Você será notificado ao ser finalizada a importação."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token expirado ou inválido"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao importar arquivo",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Erro ao iniciar importação"),
     *             @OA\Property(property="details", type="string")
     *         )
     *     )
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