<?php

namespace App\Imports;

use App\Models\Collaborator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CollaboratorImport implements ToModel, WithHeadingRow, WithChunkReading
{
    protected $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Collaborator([
            'name' => $row['name'],
            'email' => $row['email'],
            'cpf' => $row['cpf'],
            'city' => $row['city'],
            'state' => $row['state'],
            'user_id' => $this->userId, // Use a propriedade injetada
        ]);
    }

    /**
     * Define o tamanho dos chunks de um arquivo, evitando importação de partes muito grandes de uma vez só
     *
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000; // Defina o tamanho do lote conforme necessário
    }
}