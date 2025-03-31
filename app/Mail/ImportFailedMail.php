<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ImportFailedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $fileName;
    public $errorMessage;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $fileName, string $errorMessage)
    {
        $this->fileName = $fileName;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Falha na Importação de Colaboradores')
                    ->markdown('emails.import_failed')
                    ->with([
                        'fileName' => $this->fileName,
                        'errorMessage' => $this->errorMessage,
                    ]);
    }
}