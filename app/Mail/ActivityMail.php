<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class ActivityMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $title,
        public array $lines = [],
        public ?string $actionText = null,
        public ?string $actionUrl = null,
        public ?string $metaLabel = null,
        public ?string $metaValue = null,
        public ?string $orderNumber = null,
        public ?string $partnerLabel = null,
        public ?string $partnerName = null,
        public ?string $orderTotalFormatted = null,
        public array $lineItems = [],
        public ?string $statusLabel = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.activity',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
