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

    /**
     * Construtor da mensagem
     */
    public function __construct()
    {
        // Posso passar algo para a view aqui
    }

    /**
     * Assunto
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Importação de Colaboradores',
        );
    }

    /**
     * Busca a view criada do blade template
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.import_notification',
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
