<?php

declare(strict_types=1);

namespace App\Jobs\Paco;

use App\Mail\PacoLeadReceived;
use App\Models\Paco\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

final class SendLeadNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public function __construct(public readonly string $leadId) {}

    public function handle(): void
    {
        $recipient = config('paco.lead_notification_to');

        if (! is_string($recipient) || $recipient === '') {
            return;
        }

        $lead = Lead::query()
            ->with(['conversation.campaign', 'conversation.events', 'attributes'])
            ->findOrFail($this->leadId);

        Mail::to($recipient)->send(new PacoLeadReceived($lead));
    }
}
