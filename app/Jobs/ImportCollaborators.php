<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CollaboratorImport;
use Illuminate\Support\Facades\Mail;
use App\Mail\ImportNotificationMail; // E-mail de sucesso
use App\Mail\ImportFailedMail;     // E-mail de falha (crie este Mailable)

class ImportCollaborators implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $filePath;
    protected $originalName;
    protected $mimeType;
    protected $userId;

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\User  $user
     * @param  string  $filePath
     * @param  string  $originalName
     * @param  string  $mimeType
     * @return void
     */
    public function __construct(User $user, string $filePath, string $originalName, string $mimeType)
    {
        $this->user = $user;
        $this->filePath = $filePath;
        $this->originalName = $originalName;
        $this->mimeType = $mimeType;
        $this->userId = $user->id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Importe usando a classe de importação do Excel
            Excel::import(new CollaboratorImport($this->userId), $this->filePath);

            Log::info('Importação de colaboradores concluída para o usuário ' . $this->userId);
            // Invalida o cache após a importação
            Cache::forget('collaborators_' . $this->userId);

            // Envia o e-mail de sucesso
            $this->sendImportNotification(true);

        } catch (\Exception $e) {
            Log::error('Erro durante a importação de colaboradores para o usuário ' . $this->userId . ': ' . $e->getMessage());
            // Envia o e-mail de falha
            $this->sendImportNotification(false, $e->getMessage());
        } finally {
            if (Storage::disk('local')->exists(basename($this->filePath))) {
                Storage::disk('local')->delete(basename($this->filePath));
            }
        }
    }

    /**
     * Envia o e-mail de notificação de importação.
     *
     * @param  bool  $success
     * @param  string|null  $errorMessage
     * @return void
     */
    protected function sendImportNotification(bool $success, string $errorMessage = null)
    {
        if ($success) {
            Mail::to($this->user->email)->send(new ImportNotificationMail($this->originalName));
            Log::info('E-mail de notificação de importação (sucesso) enviado para o usuário ' . $this->user->email);
        } else {
            Mail::to($this->user->email)->send(new ImportFailedMail($this->originalName, $errorMessage));
            Log::error('E-mail de notificação de importação (falha) enviado para o usuário ' . $this->user->email);
        }
    }
}