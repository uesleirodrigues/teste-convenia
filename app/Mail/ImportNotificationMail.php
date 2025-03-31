<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ImportNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $fileName;

    /**
     * Construtor da mensagem
     *
     * @param string $fileName
     */
    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * Assunto
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Importação de Colaboradores Concluída',
        );
    }

    /**
     * Busca a view criada do blade template
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.import_notification',
            with: [
                'fileName' => $this->fileName,
            ],
        );
    }

    /**
     * Possíveis anexos aqui
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}