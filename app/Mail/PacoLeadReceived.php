<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Paco\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class PacoLeadReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Lead $lead) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva consulta de '.($this->lead->name ?: 'un visitante').' · Socies',
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.paco.lead-received',
        );
    }
}
