<?php

namespace App\Jobs;

use App\Mail\ActivityMail;
use App\Models\MailDispatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendActivityMailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public int $mailDispatchId,
        public string $recipientEmail,
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

    public function handle(): void
    {
        $dispatch = MailDispatch::find($this->mailDispatchId);
        if (! $dispatch || $dispatch->sent_at) {
            return;
        }

        Mail::to($this->recipientEmail)->send(new ActivityMail(
            subjectLine: $this->subjectLine,
            title: $this->title,
            lines: $this->lines,
            actionText: $this->actionText,
            actionUrl: $this->actionUrl,
            metaLabel: $this->metaLabel,
            metaValue: $this->metaValue,
            orderNumber: $this->orderNumber,
            partnerLabel: $this->partnerLabel,
            partnerName: $this->partnerName,
            orderTotalFormatted: $this->orderTotalFormatted,
            lineItems: $this->lineItems,
            statusLabel: $this->statusLabel,
        ));

        $dispatch->update(['sent_at' => now()]);
    }

    public function failed(\Throwable $e): void
    {
        Log::warning('Activity mail job failed', [
            'mail_dispatch_id' => $this->mailDispatchId,
            'recipient' => $this->recipientEmail,
            'error' => $e->getMessage(),
        ]);
    }
}
