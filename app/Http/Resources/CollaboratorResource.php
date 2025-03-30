<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Collaborator",
 *     title="Collaborator",
 *     description="Modelo de um colaborador",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="João da Silva"),
 *     @OA\Property(property="email", type="string", example="joao@email.com"),
 *     @OA\Property(property="cpf", type="string", example="123.456.789-09"),
 *     @OA\Property(property="city", type="string", example="São Paulo"),
 *     @OA\Property(property="state", type="string", example="SP"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-29 14:30:00"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-29 15:00:00")
 * )
 */
class CollaboratorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'     => $this->id,
            'name'   => $this->name,
            'email'  => $this->email,
            'cpf'    => $this->cpf,
            'city'   => $this->city,
            'state'  => $this->state,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
