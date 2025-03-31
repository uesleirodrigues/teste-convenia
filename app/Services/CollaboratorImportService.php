<?php

namespace App\Services;

use App\Imports\CollaboratorImport;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Mail\ImportNotificationMail;
use App\Models\User;

class CollaboratorImportService
{
    public function importAndNotify(User $user, $file)
    {
        try {
            // Importando os colaboradores
            Excel::import(new CollaboratorImport($user), $file);

            // Enviando o e-mail de notificação
            Mail::to($user->email)->send(new ImportNotificationMail());

            return ['status' => 'success', 'message' => 'Processamento realizado com sucesso!'];
        } catch (\Exception $e) {
            // Log de erro
            \Log::error('Erro na importação de colaboradores: ' . $e->getMessage());
            throw new \Exception('Falha ao importar colaboradores. Tente novamente mais tarde.');
        }
    }
}
