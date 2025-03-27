<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Collaborator extends Model
{
    protected $fillable = [
        'name',
        'email',
        'cpf',
        'city',
        'state',
        'user_id', // FK para o Gestor
    ];

    /**
     * Relacionamento com o usuário (um colaborador pertence a um usuário).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
